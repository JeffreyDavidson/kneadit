<?php

namespace App\Http\Controllers;

use App\Models\CapacityLimit;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\CustomerFavorite;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index()
    {
        $categories = Category::with(['products' => function ($q) {
            $q->where('is_available', true)->orderBy('sort_order');
        }])->orderBy('sort_order')->get();

        return view('order', compact('categories'));
    }

    public function home()
    {
        $categories = Category::with(['products' => function ($q) {
            $q->where('is_available', true)->orderBy('sort_order');
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
            'subtotal' => 'required|numeric|min:0'
        ]);

        $code = strtoupper(trim($request->input('code')));
        $subtotal = (float) $request->input('subtotal');

        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return response()->json(['error' => 'Coupon not found.'], 422);
        }

        if (!$coupon->isValid()) {
            return response()->json(['error' => 'This coupon is no longer valid.'], 422);
        }

        if ($coupon->min_order_amount && $subtotal < (float) $coupon->min_order_amount) {
            return response()->json([
                'error' => 'Minimum order of $' . number_format($coupon->min_order_amount, 2) . ' required for this coupon.',
            ], 422);
        }

        $discount = $coupon->calculateDiscount($subtotal);

        return response()->json([
            'success' => true,
            'coupon_id' => $coupon->id,
            'code' => $coupon->code,
            'discount' => $discount,
            'label' => $coupon->type === 'percentage'
                ? number_format($coupon->value, 0) . '% off'
                : '$' . number_format($coupon->value, 2) . ' off',
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
        if (!CapacityLimit::isAvailable($validated['delivery_date'])) {
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
            [
                'name' => $validated['customer_name'],
                'phone' => $validated['customer_phone'] ?? null,
            ]
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
        if (!empty($calculated['coupon_id'])) {
            Coupon::where('id', $calculated['coupon_id'])->increment('used_count');
        }

        // Create order items
        foreach ($calculated['items'] as $item) {
            $order->orderItems()->create($item);
        }

        return redirect()->route('order.confirmation', $order->order_number)
            ->with('success', 'Order submitted successfully!');
    }

    /**
     * Check capacity for a specific date
     */
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
            'delivery_type' => 'required|in:pickup,delivery',
            'delivery_address' => 'required_if:delivery_type,delivery|nullable|string|max:500',
            'delivery_date' => 'required|date|after_or_equal:' . now()->addDays(2)->toDateString(),
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
            if (!$product->is_available) continue;

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
            $number = 'KN' . date('ymd') . strtoupper(Str::random(4));
        } while (Order::where('order_number', $number)->exists());

        return $number;
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
            ->with('orderItems.product')
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
        if (!$email) {
            return response()->json(['favorites' => []]);
        }

        $favorites = CustomerFavorite::forCustomer($email)
            ->pluck('product_id')
            ->toArray();

        return response()->json(['favorites' => $favorites]);
    }
}