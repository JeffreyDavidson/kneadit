<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CapacityLimit;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\CustomerFavorite;
use App\Models\GalleryPhoto;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\Setting;
use App\Models\WaitlistEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StorefrontApiController extends Controller
{
    public function store(): JsonResponse
    {
        return response()->json([
            'data' => [
                'store_name' => Setting::get('store_name', ''),
                'tagline' => Setting::get('tagline', ''),
                'phone' => Setting::get('phone', ''),
                'email' => Setting::get('email', ''),
                'address' => Setting::get('address', ''),
                'logo_url' => Setting::get('logo_url', ''),
                'colors' => [
                    'primary' => Setting::get('color_primary', '#e11d48'),
                    'secondary' => Setting::get('color_secondary', '#be123c'),
                    'accent' => Setting::get('color_accent', '#f43f5e'),
                    'light' => Setting::get('color_light', '#fff1f2'),
                    'border' => Setting::get('color_border', '#fecdd3'),
                    'muted' => Setting::get('color_muted', '#6b7280'),
                ],
                'hours' => Setting::get('hours', ''),
                'social_links' => [
                    'facebook' => Setting::get('social_facebook', ''),
                    'instagram' => Setting::get('social_instagram', ''),
                    'twitter' => Setting::get('social_twitter', ''),
                    'tiktok' => Setting::get('social_tiktok', ''),
                ],
            ],
            'message' => 'Store info retrieved successfully.',
        ]);
    }

    public function categories(): JsonResponse
    {
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug', 'description', 'sort_order']);

        return response()->json([
            'data' => $categories,
            'message' => 'Categories retrieved successfully.',
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $query = Product::where('is_active', true)->with('category');

        if ($request->has('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->input('category')));
        }

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        $products = $query->get()->map(fn (Product $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'description' => $p->description,
            'price' => $p->price,
            'image' => $p->image,
            'category_id' => $p->category_id,
            'category_name' => $p->category?->name,
            'is_featured' => $p->is_featured,
        ]);

        return response()->json([
            'data' => $products,
            'message' => 'Products retrieved successfully.',
        ]);
    }

    public function menu(): JsonResponse
    {
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->with(['products' => fn ($q) => $q->where('is_active', true)])
            ->get()
            ->map(fn (Category $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'description' => $c->description,
                'sort_order' => $c->sort_order,
                'products' => $c->products->map(fn (Product $p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'description' => $p->description,
                    'price' => $p->price,
                    'image' => $p->image,
                    'is_featured' => $p->is_featured,
                ]),
            ]);

        return response()->json([
            'data' => $categories,
            'message' => 'Menu retrieved successfully.',
        ]);
    }

    public function capacity(string $date): JsonResponse
    {
        return response()->json([
            'data' => [
                'available' => CapacityLimit::isAvailable($date),
                'remaining' => CapacityLimit::remainingSlots($date),
                'max' => CapacityLimit::getMaxOrders($date),
            ],
            'message' => 'Capacity retrieved successfully.',
        ]);
    }

    public function submitOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:50',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.special_instructions' => 'nullable|string|max:500',
            'requested_date' => 'required|date|after_or_equal:today',
            'requested_time' => 'nullable|string',
            'delivery_address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'coupon_code' => 'nullable|string',
        ]);

        $customer = Customer::firstOrCreate(
            ['email' => $validated['customer_email']],
            [
                'name' => $validated['customer_name'],
                'phone' => $validated['customer_phone'],
            ]
        );

        $subtotal = 0;
        $itemsData = [];

        foreach ($validated['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            $lineTotal = $product->price * $item['quantity'];
            $subtotal += $lineTotal;
            $itemsData[] = [
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'unit_price' => $product->price,
                'special_instructions' => $item['special_instructions'] ?? null,
            ];
        }

        $discount = 0;
        if (!empty($validated['coupon_code'])) {
            $coupon = Coupon::where('code', strtoupper($validated['coupon_code']))->valid()->first();
            if ($coupon) {
                $discount = $coupon->calculateDiscount($subtotal);
                $coupon->increment('used_count');
            }
        }

        $total = max(0, $subtotal - $discount);

        $order = Order::create([
            'customer_id' => $customer->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
            'requested_date' => $validated['requested_date'],
            'requested_time' => $validated['requested_time'] ?? null,
            'delivery_address' => $validated['delivery_address'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        foreach ($itemsData as $itemData) {
            $order->orderItems()->create($itemData);
        }

        return response()->json([
            'data' => [
                'order_number' => $order->order_number,
                'total' => $order->total,
                'status' => $order->status,
            ],
            'message' => 'Order submitted successfully.',
        ], 201);
    }

    public function validateCoupon(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', strtoupper($validated['code']))->valid()->first();

        if (!$coupon) {
            return response()->json([
                'data' => ['valid' => false, 'discount_amount' => 0, 'type' => null, 'value' => null],
                'message' => 'Invalid or expired coupon.',
            ]);
        }

        return response()->json([
            'data' => [
                'valid' => true,
                'discount_amount' => $coupon->calculateDiscount((float) $validated['subtotal']),
                'type' => $coupon->type,
                'value' => $coupon->value,
            ],
            'message' => 'Coupon is valid.',
        ]);
    }

    public function reviews(Request $request): JsonResponse
    {
        $query = Review::where('is_approved', true)->with('product');

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        $reviews = $query->latest()->get()->map(fn (Review $r) => [
            'customer_name' => $r->customer_name,
            'rating' => $r->rating,
            'comment' => $r->comment,
            'product_name' => $r->product?->name,
            'created_at' => $r->created_at->toISOString(),
        ]);

        return response()->json([
            'data' => $reviews,
            'message' => 'Reviews retrieved successfully.',
        ]);
    }

    public function submitReview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:2000',
        ]);

        $review = Review::create([
            ...$validated,
            'is_approved' => false,
        ]);

        return response()->json([
            'data' => ['id' => $review->id],
            'message' => 'Review submitted and pending approval.',
        ], 201);
    }

    public function gallery(): JsonResponse
    {
        $photos = GalleryPhoto::visible()->ordered()->get(['id', 'title', 'image_path', 'category']);

        return response()->json([
            'data' => $photos,
            'message' => 'Gallery retrieved successfully.',
        ]);
    }

    public function submitContact(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        ContactMessage::create($validated);

        return response()->json([
            'data' => null,
            'message' => 'Message sent successfully.',
        ], 201);
    }

    public function favorites(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $productIds = CustomerFavorite::forCustomer($request->input('email'))
            ->pluck('product_id');

        return response()->json([
            'data' => $productIds,
            'message' => 'Favorites retrieved successfully.',
        ]);
    }

    public function toggleFavorite(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'product_id' => 'required|exists:products,id',
        ]);

        $favorited = CustomerFavorite::toggle($validated['email'], $validated['product_id']);

        return response()->json([
            'data' => ['favorited' => $favorited],
            'message' => $favorited ? 'Added to favorites.' : 'Removed from favorites.',
        ]);
    }

    public function waitlist(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:50',
            'requested_date' => 'required|date',
            'product_id' => 'nullable|exists:products,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $entry = WaitlistEntry::create([
            ...$validated,
            'status' => 'waiting',
        ]);

        return response()->json([
            'data' => ['id' => $entry->id],
            'message' => 'Added to waitlist successfully.',
        ], 201);
    }
}
