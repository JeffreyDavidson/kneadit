<?php

namespace App\Http\Controllers\Api;

use App\Enums\DeliveryType;
use App\Enums\WaitlistStatus;
use App\Http\Controllers\Controller;
use App\Models\CapacityLimit;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\CustomerFavorite;
use App\Models\GalleryPhoto;
use App\Models\Product;
use App\Models\Review;
use App\Models\Setting;
use App\Models\WaitlistEntry;
use App\Services\CouponService;
use App\Services\OrderService;
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

    public function submitOrder(Request $request, OrderService $orderService): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:50',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.special_instructions' => 'nullable|string|max:500',
            'delivery_date' => 'required|date|after_or_equal:today',
            'delivery_time' => 'nullable|string',
            'delivery_type' => 'nullable|in:pickup,delivery',
            'delivery_address' => 'nullable|string|max:500',
            'delivery_tier' => 'nullable|in:under5,5to10,10to15,over15',
            'notes' => 'nullable|string|max:1000',
            'coupon_code' => 'nullable|string',
        ]);

        // Resolve coupon ID from code if provided
        $couponId = null;
        if (! empty($validated['coupon_code'])) {
            $couponService = new CouponService;
            $result = $couponService->validate($validated['coupon_code'], 0);
            if ($result['valid']) {
                $couponId = $result['coupon']->id;
            }
        }

        // Default delivery_type to pickup for API requests that omit it
        $validated['delivery_type'] = $validated['delivery_type'] ?? DeliveryType::Pickup->value;

        $order = $orderService->createOrder($validated, $couponId);

        if (! $order) {
            return response()->json([
                'data' => null,
                'message' => 'This date is fully booked or no valid items in order.',
            ], 422);
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

        $couponService = new CouponService;
        $result = $couponService->validate($validated['code'], (float) $validated['subtotal']);

        if (! $result['valid']) {
            return response()->json([
                'data' => ['valid' => false, 'discount_amount' => 0, 'type' => null, 'value' => null],
                'message' => $result['error'],
            ]);
        }

        $coupon = $result['coupon'];

        return response()->json([
            'data' => [
                'valid' => true,
                'discount_amount' => $result['discount'],
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

        $validated['comment'] = strip_tags($validated['comment']);

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

        $validated['subject'] = strip_tags($validated['subject']);
        $validated['message'] = strip_tags($validated['message']);

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
            'delivery_date' => 'required|date',
            'product_id' => 'nullable|exists:products,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        if (isset($validated['notes'])) {
            $validated['notes'] = strip_tags($validated['notes']);
        }

        $entry = WaitlistEntry::create([
            ...$validated,
            'status' => WaitlistStatus::Waiting,
        ]);

        return response()->json([
            'data' => ['id' => $entry->id],
            'message' => 'Added to waitlist successfully.',
        ], 201);
    }
}
