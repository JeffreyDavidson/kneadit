<?php

namespace App\Http\Controllers;

use App\Enums\CouponType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Requests\StoreOrderRequest;
use App\Mail\NewOrderMessage;
use App\Models\BlockedDate;
use App\Models\BusinessSchedule;
use App\Models\CapacityLimit;
use App\Models\Category;
use App\Models\CustomerFavorite;
use App\Models\Order;
use App\Models\Setting;
use App\Services\CouponService;
use App\Services\GiftCardService;
use App\Services\OrderService;
use App\Services\StripeCheckoutService;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Mail;

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

    /**
     * Validate and apply a coupon code via AJAX.
     */
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
            'subtotal' => ['required', 'numeric', 'min:0'],
        ]);

        $couponService = new CouponService;
        $result = $couponService->validate($request->input('code'), (float) $request->input('subtotal'));

        if (! $result['valid']) {
            return response()->json(['error' => $result['error']], 422);
        }

        $coupon = $result['coupon'];

        return response()->json([
            'success' => true,
            'coupon_id' => $coupon->id,
            'code' => $coupon->code,
            'discount_amount' => $result['discount'],
            'label' => $coupon->type === CouponType::Percentage
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
            'code' => ['required', 'string'],
            'subtotal' => ['required', 'numeric', 'min:0'],
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
     * Submit an order, delegating business logic to OrderService.
     */
    public function store(StoreOrderRequest $request, OrderService $orderService)
    {
        $validated = $request->validated();

        $order = $orderService->createOrder(
            $validated,
            $request->input('coupon_id'),
            $request->input('gift_card_id'),
        );

        if (! $order) {
            return back()->withErrors(['delivery_date' => 'Sorry, this date is fully booked. Please choose another date.']);
        }

        // If baker has Stripe Connect enabled and order total > 0, redirect to Stripe Checkout
        if ($order->total > 0 && StripeCheckoutService::isEnabled()) {
            $stripeService = new StripeCheckoutService;
            $session = $stripeService->createCheckoutSession(
                $order,
                route('order.stripe.success', $order).'?session_id={CHECKOUT_SESSION_ID}',
                route('order.stripe.cancel', $order),
            );

            if ($session) {
                return redirect($session->url);
            }

            // Stripe session creation failed — fall through to normal confirmation
        }

        return to_route('order.confirmation', $order)
            ->with('success', 'Order submitted successfully!');
    }

    /**
     * Stripe checkout success callback.
     */
    public function stripeSuccess(Request $request, Order $order)
    {
        $sessionId = $request->query('session_id');

        if ($sessionId) {
            $stripeService = new StripeCheckoutService;
            $stripeService->handleCheckoutComplete($sessionId);
        }

        return to_route('order.confirmation', $order)
            ->with('success', 'Payment successful! Your order has been placed.');
    }

    /**
     * Stripe checkout cancel callback.
     */
    public function stripeCancel(Order $order)
    {
        $order->update(['payment_status' => PaymentStatus::Unpaid]);

        return to_route('order.confirmation', $order)
            ->with('warning', 'Payment was not completed. You can pay later or contact the baker.');
    }

    /**
     * Return availability for the next 30 days.
     */
    public function availability()
    {
        $dates = [];
        $today = Date::today();

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
                ?? (int) Setting::get('default_daily_capacity', 100);
            $currentOrders = Order::whereDate('delivery_date', $dateStr)
                ->whereNotIn('status', [OrderStatus::Cancelled])
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
            $carbon = Date::parse($date);
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

    public function confirmation(Order $order)
    {
        $order->load('orderItems');

        return view('order-confirmation', compact('order'));
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
        $request->validate(['email' => ['required', 'email']]);

        $orders = Order::whereHas('customer', function (Builder $q) use ($request) {
            $q->where('email', $request->email);
        })
            ->with(['orderItems.product', 'messages'])
            ->latest()
            ->limit(50)
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
            'customer_email' => ['required', 'email'],
            'product_id' => ['required', 'exists:products,id'],
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
            'message' => ['required', 'string', 'max:2000'],
            'sender_name' => ['required', 'string', 'max:255'],
            'sender_email' => ['required', 'email'],
        ]);

        $message = $order->messages()->create([
            'sender_type' => 'customer',
            'sender_name' => $request->sender_name,
            'message' => $request->message,
        ]);

        // Email the baker
        $storeEmail = Setting::get('store_email');
        if ($storeEmail) {
            Mail::to($storeEmail)
                ->send(new NewOrderMessage($message));
        }

        return response()->json(['success' => true, 'message' => $message]);
    }
}
