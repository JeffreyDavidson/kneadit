<?php

namespace App\Http\Controllers;

use App\Models\BlockedDate;
use App\Models\BusinessSchedule;
use App\Models\CapacityLimit;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\CustomerFavorite;
use App\Models\GiftCard;
use App\Models\Order;
use App\Models\Product;
use App\Services\GiftCardService;
use App\Services\StripeCheckoutService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index()
    {
        $categories = Category::with(['products' => function ($q) {
            $q->where('is_active', true)->orderBy('name');
        }, 'products.seasonalItems'])->orderBy('sort_order')->get();

        // Filter out products that are seasonal but not currently in season
        $categories->each(function ($category) {
            $category->setRelation(
                'products',
                $category->products->filter(fn ($product) => $product->isInSeason())
            );
        });

        return view('order', compact('categories'));
    }

    public function home()
    {
        $categories = Category::with(['products' => function ($q) {
            $q->where('is_active', true)->orderBy('name');
        }])->orderBy('sort_order')->get();

        return view('home', compact('categories'));
    }

    /**
     * Validate and apply a coupon code via AJAX.
     */
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $code = strtoupper(trim($request->input('code')));
        $subtotal = (float) $request->input('subtotal');

        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon) {
            return response()->json(['error' => 'Coupon not found.'], 422);
        }

        if (! $coupon->isValid()) {
            return response()->json(['error' => 'This coupon is no longer valid.'], 422);
        }

        if ($coupon->min_order_amount && $subtotal < (float) $coupon->min_order_amount) {
            return response()->json([
                'error' => 'Minimum order of $'.number_format($coupon->min_order_amount, 2).' required for this coupon.',
            ], 422);
        }

        $discount = $coupon->calculateDiscount($subtotal);

        return response()->json([
            'success' => true,
            'coupon_id' => $coupon->id,
            'code' => $coupon->code,
            'discount_amount' => $discount,
            'label' => $coupon->type === 'percentage'
                ? number_format($coupon->value, 0).'% off'
                : '$'.number_format($coupon->value, 2).' off',
        ]);
    }

    /**
     * Validate and apply a gift card code via AJAX.
     */
    public function applyGiftCard(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $service = new GiftCardService;
        $card = $service->checkBalance($request->code);

        if (! $card) {
            return response()->json(['error' => 'Gift card not found.'], 422);
        }

        if (! $card->isUsable()) {
            return response()->json(['error' => 'This gift card is no longer valid.'], 422);
        }

        $applicable = min((float) $card->current_balance, (float) $request->subtotal);

        return response()->json([
            'success' => true,
            'gift_card_id' => $card->id,
            'code' => $card->code,
            'available_balance' => (float) $card->current_balance,
            'applicable_amount' => $applicable,
        ]);
    }

    /**
     * Submit order (simplified without payment processing for now)
     */
    public function store(Request $request)
    {
        $validated = $this->validateOrder($request);
        $calculated = $this->calculateOrder($validated);

        if (empty($calculated['items'])) {
            return back()->withErrors(['items' => 'No valid items in order.']);
        }

        // Check capacity limits
        if (! CapacityLimit::isAvailable($validated['delivery_date'])) {
            return back()->withErrors(['delivery_date' => 'Sorry, this date is fully booked. Please choose another date.']);
        }

        // Apply coupon if provided
        $couponId = $request->input('coupon_id');
        $discountAmount = 0;
        if ($couponId) {
            $coupon = Coupon::find($couponId);
            if ($coupon && $coupon->isValid()) {
                $discountAmount = $coupon->calculateDiscount($calculated['subtotal']);
                $calculated['discount_amount'] = $discountAmount;
                $calculated['coupon_id'] = $coupon->id;
                $calculated['total'] = max(0, $calculated['total'] - $discountAmount);
            }
        }

        // Create or update customer
        $customer = Customer::updateOrCreate(
            ['email' => $validated['customer_email']],
            array_filter([
                'name' => $validated['customer_name'],
                'phone' => $validated['customer_phone'] ?? null,
                'birthday' => $validated['customer_birthday'] ?? null,
            ], fn ($v) => $v !== null)
        );

        // Create order
        $order = Order::create([
            'customer_id' => $customer->id,
            'order_number' => $this->generateOrderNumber(),
            'delivery_date' => $validated['delivery_date'],
            'delivery_time' => $validated['delivery_time'] ?? null,
            'delivery_type' => $validated['delivery_type'],
            'delivery_address' => $validated['delivery_address'] ?? null,
            'subtotal' => $calculated['subtotal'],
            'delivery_fee' => $calculated['delivery_fee'],
            'discount_amount' => $calculated['discount_amount'] ?? 0,
            'coupon_id' => $calculated['coupon_id'] ?? null,
            'total' => $calculated['total'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        // Increment coupon usage
        if (! empty($calculated['coupon_id'])) {
            Coupon::where('id', $calculated['coupon_id'])->increment('used_count');
        }

        // Redeem gift card if provided
        $giftCardId = $request->input('gift_card_id');
        if ($giftCardId) {
            $giftCard = GiftCard::find($giftCardId);
            if ($giftCard && $giftCard->isUsable()) {
                $gcAmount = min((float) $giftCard->current_balance, (float) $order->total);
                if ($gcAmount > 0) {
                    $service = new GiftCardService;
                    $service->redeem($giftCard->code, $gcAmount, $order->id);
                    $order->update(['total' => max(0, (float) $order->total - $gcAmount)]);
                }
            }
        }

        // Create order items
        foreach ($calculated['items'] as $item) {
            $order->orderItems()->create($item);
        }

        // If baker has Stripe Connect enabled and order total > 0, redirect to Stripe Checkout
        if ($order->total > 0 && StripeCheckoutService::isEnabled()) {
            $stripeService = new StripeCheckoutService;
            $session = $stripeService->createCheckoutSession(
                $order,
                route('order.stripe.success', $order->order_number) . '?session_id={CHECKOUT_SESSION_ID}',
                route('order.stripe.cancel', $order->order_number),
            );

            if ($session) {
                return redirect($session->url);
            }

            // Stripe session creation failed — fall through to normal confirmation
        }

        return redirect()->route('order.confirmation', $order->order_number)
            ->with('success', 'Order submitted successfully!');
    }

    /**
     * Stripe checkout success callback.
     */
    public function stripeSuccess(Request $request, string $orderNumber)
    {
        $sessionId = $request->query('session_id');

        if ($sessionId) {
            $stripeService = new StripeCheckoutService;
            $stripeService->handleCheckoutComplete($sessionId);
        }

        return redirect()->route('order.confirmation', $orderNumber)
            ->with('success', 'Payment successful! Your order has been placed.');
    }

    /**
     * Stripe checkout cancel callback.
     */
    public function stripeCancel(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->first();

        if ($order) {
            $order->update(['payment_status' => 'unpaid']);
        }

        return redirect()->route('order.confirmation', $orderNumber)
            ->with('warning', 'Payment was not completed. You can pay later or contact the baker.');
    }

    /**
     * Return availability for the next 30 days.
     */
    public function availability()
    {
        $dates = [];
        $today = Carbon::today();

        for ($i = 0; $i < 30; $i++) {
            $date = $today->copy()->addDays($i);
            $dayOfWeek = (int) $date->dayOfWeek;
            $dateStr = $date->toDateString();

            $schedule = BusinessSchedule::forDay($dayOfWeek);
            $isOpen = $schedule?->is_open ?? false;

            // Check blocked dates
            $blocked = BlockedDate::where('date', $dateStr)->where('is_all_day', true)->first();

            if ($blocked) {
                $dates[] = [
                    'date' => $dateStr,
                    'available' => false,
                    'reason' => $blocked->reason ?? 'Blocked',
                    'remaining_capacity' => 0,
                ];

                continue;
            }

            if (! $isOpen) {
                $dates[] = [
                    'date' => $dateStr,
                    'available' => false,
                    'reason' => 'Closed',
                    'remaining_capacity' => 0,
                ];

                continue;
            }

            // Check capacity
            $maxOrders = $schedule->max_orders
                ?? (int) \App\Models\Setting::get('default_daily_capacity', 100);
            $currentOrders = Order::whereDate('delivery_date', $dateStr)
                ->whereNotIn('status', ['cancelled'])
                ->count();
            $remaining = max(0, $maxOrders - $currentOrders);

            $dates[] = [
                'date' => $dateStr,
                'available' => $remaining > 0,
                'reason' => $remaining > 0 ? 'Open' : 'Fully booked',
                'remaining_capacity' => $remaining,
            ];
        }

        return response()->json($dates);
    }

    public function checkCapacity(string $date)
    {
        try {
            $carbon = Carbon::parse($date);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date'], 422);
        }

        $available = CapacityLimit::isAvailable($carbon);
        $remaining = CapacityLimit::remainingSlots($carbon);
        $maxOrders = CapacityLimit::getMaxOrders($carbon);

        return response()->json([
            'available' => $available,
            'remaining' => $remaining,
            'max_orders' => $maxOrders,
            'usage_percent' => CapacityLimit::usagePercent($carbon),
        ]);
    }

    public function confirmation(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->with('orderItems')->firstOrFail();

        return view('order-confirmation', compact('order'));
    }

    protected function validateOrder(Request $request): array
    {
        return $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_birthday' => 'nullable|date',
            'delivery_type' => 'required|in:pickup,delivery',
            'delivery_address' => 'required_if:delivery_type,delivery|nullable|string|max:500',
            'delivery_date' => 'required|date|after_or_equal:'.now()->addDays(2)->toDateString(),
            'delivery_time' => 'nullable|string|max:20',
            'delivery_tier' => 'required_if:delivery_type,delivery|nullable|in:under5,5to10,10to15,over15',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:20',
        ]);
    }

    protected function calculateOrder(array $validated): array
    {
        $subtotal = 0;
        $orderItems = [];

        foreach ($validated['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            if (! $product->is_active) {
                continue;
            }

            $lineTotal = $product->price * $item['quantity'];
            $subtotal += $lineTotal;

            $orderItems[] = [
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'unit_price' => $product->price,
                'total_price' => $lineTotal,
            ];
        }

        $deliveryFee = 0;
        if ($validated['delivery_type'] === 'delivery') {
            $deliveryFee = match ($validated['delivery_tier'] ?? null) {
                'under5' => 0,
                '5to10' => 5.00,
                '10to15' => 10.00,
                'over15' => 15.00,
                default => 0,
            };
        }

        return [
            'items' => $orderItems,
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'total' => $subtotal + $deliveryFee,
        ];
    }

    protected function generateOrderNumber(): string
    {
        do {
            $number = 'KN'.date('ymd').strtoupper(Str::random(4));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }

    public function reorderData(Order $order)
    {
        $items = $order->orderItems->map(fn ($item) => [
            'product_id' => $item->product_id,
            'product_name' => $item->product?->name ?? 'Unknown',
            'price' => $item->unit_price,
            'quantity' => $item->quantity,
        ]);

        return response()->json(['items' => $items]);
    }

    public function track()
    {
        return view('order-tracking');
    }

    public function trackLookup(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $orders = Order::whereHas('customer', function ($q) use ($request) {
            $q->where('email', $request->email);
        })
            ->with(['orderItems.product', 'messages'])
            ->latest()
            ->get();

        return view('order-tracking', [
            'orders' => $orders,
            'email' => $request->email,
        ]);
    }

    /**
     * Toggle customer favorite
     */
    public function toggleFavorite(Request $request)
    {
        $validated = $request->validate([
            'customer_email' => 'required|email',
            'product_id' => 'required|exists:products,id',
        ]);

        $isFavorite = CustomerFavorite::toggle(
            $validated['customer_email'],
            $validated['product_id']
        );

        return response()->json([
            'success' => true,
            'is_favorite' => $isFavorite,
        ]);
    }

    /**
     * Get customer favorites
     */
    public function getFavorites(Request $request)
    {
        $email = $request->input('email');
        if (! $email) {
            return response()->json(['favorites' => []]);
        }

        $favorites = CustomerFavorite::forCustomer($email)
            ->pluck('product_id')
            ->toArray();

        return response()->json(['favorites' => $favorites]);
    }

    public function messages(Order $order)
    {
        $messages = $order->messages()->oldest()->get();

        return response()->json(['messages' => $messages]);
    }

    public function sendMessage(Request $request, Order $order)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'sender_name' => 'required|string|max:255',
            'sender_email' => 'required|email',
        ]);

        $message = $order->messages()->create([
            'sender_type' => 'customer',
            'sender_name' => $request->sender_name,
            'message' => $request->message,
        ]);

        // Email the baker
        $storeEmail = \App\Models\Setting::get('store_email');
        if ($storeEmail) {
            \Illuminate\Support\Facades\Mail::to($storeEmail)
                ->send(new \App\Mail\NewOrderMessage($message));
        }

        return response()->json(['success' => true, 'message' => $message]);
    }
}
