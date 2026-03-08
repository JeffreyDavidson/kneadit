<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerFavorite;
use App\Models\CustomerPhoto;
use App\Models\LoyaltyReward;
use App\Models\Product;
use App\Models\Setting;
use App\Services\GiftCardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StorefrontController extends Controller
{
    public function home()
    {
        $categories = Category::where('is_active', true)
            ->with(['products' => fn($q) => $q->where('is_active', true)->where('is_featured', true)])
            ->orderBy('sort_order')
            ->get();

        $storeName = Setting::get('store_name', 'Our Bakery');

        return view('home', compact('categories', 'storeName'));
    }

    public function menu()
    {
        $categories = Category::where('is_active', true)
            ->with(['products' => fn($q) => $q->where('is_active', true)->orderBy('name'), 'products.seasonalItems'])
            ->orderBy('sort_order')
            ->get();

        $storeName = Setting::get('store_name', 'Our Bakery');

        return view('menu', compact('categories', 'storeName'));
    }

    public function about()
    {
        return view('about');
    }

    public function reviews()
    {
        $reviews = \App\Models\Review::where('is_approved', true)
            ->with('product')
            ->latest()
            ->paginate(12);

        $avgRating = \App\Models\Review::where('is_approved', true)->avg('rating');
        $totalReviews = \App\Models\Review::where('is_approved', true)->count();

        return view('reviews', compact('reviews', 'avgRating', 'totalReviews'));
    }

    public function rewards()
    {
        $rewards = LoyaltyReward::where('is_active', true)->orderBy('points_required')->get();
        $programName = Setting::get('loyalty_program_name', 'Rewards');
        $pointsPerDollar = Setting::get('loyalty_points_per_dollar', '10');
        $loyaltyEnabled = Setting::get('loyalty_enabled', '1') === '1';

        return view('loyalty', compact('rewards', 'programName', 'pointsPerDollar', 'loyaltyEnabled'));
    }

    public function checkRewards(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $customer = Customer::where('email', $request->email)->first();
        $rewards = LoyaltyReward::where('is_active', true)->orderBy('points_required')->get();
        $programName = Setting::get('loyalty_program_name', 'Rewards');
        $pointsPerDollar = Setting::get('loyalty_points_per_dollar', '10');
        $loyaltyEnabled = Setting::get('loyalty_enabled', '1') === '1';

        $totalPoints = $customer ? $customer->total_points : 0;
        $lifetimeEarned = $customer ? $customer->lifetime_points_earned : 0;
        $history = $customer ? $customer->loyaltyPoints()->latest('created_at')->limit(20)->get() : collect();

        return view('loyalty', compact(
            'rewards', 'programName', 'pointsPerDollar', 'loyaltyEnabled',
            'customer', 'totalPoints', 'lifetimeEarned', 'history'
        ));
    }

    public function getFavorites(Request $request)
    {
        $email = $request->query('email');

        if (! $email) {
            return response()->json(['favorites' => []]);
        }

        $favorites = CustomerFavorite::where('customer_email', $email)
            ->pluck('product_id')
            ->toArray();

        return response()->json(['favorites' => $favorites]);
    }

    public function toggleFavorite(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'product_id' => 'required|exists:products,id',
        ]);

        $existing = CustomerFavorite::where('customer_email', $request->email)
            ->where('product_id', $request->product_id)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['favorited' => false]);
        }

        CustomerFavorite::create([
            'customer_email' => $request->email,
            'product_id' => $request->product_id,
        ]);

        return response()->json(['favorited' => true]);
    }

    public function manifest()
    {
        $storeName = Setting::get('store_name', 'Our Bakery');
        $primaryColor = tenant()->brand_color_primary ?? '#d4920c';

        return response()->json([
            'name' => $storeName,
            'short_name' => $storeName,
            'description' => Setting::get('business_tagline', 'Fresh baked goods made with love'),
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => '#fef9ef',
            'theme_color' => $primaryColor,
            'icons' => [
                ['src' => '/icons/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png'],
                ['src' => '/icons/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png'],
            ],
        ])->header('Content-Type', 'application/manifest+json');
    }

    public function appIcon($size)
    {
        $size = in_array($size, ['192', '512']) ? (int) $size : 192;
        $storeName = Setting::get('store_name', 'B');
        $color = tenant()->brand_color_primary ?? '#d4920c';

        $img = imagecreatetruecolor($size, $size);
        $r = hexdec(substr($color, 1, 2));
        $g = hexdec(substr($color, 3, 2));
        $b = hexdec(substr($color, 5, 2));
        $bgColor = imagecolorallocate($img, $r, $g, $b);
        $textColor = imagecolorallocate($img, 255, 255, 255);
        imagefill($img, 0, 0, $bgColor);

        $letter = strtoupper(substr($storeName, 0, 1));
        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($letter);
        $textHeight = imagefontheight($font);
        imagestring($img, $font, (int) (($size - $textWidth) / 2), (int) (($size - $textHeight) / 2), $letter, $textColor);

        ob_start();
        imagepng($img);
        $data = ob_get_clean();
        imagedestroy($img);

        return response($data)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'public, max-age=86400');
    }

    public function giftCards()
    {
        return view('gift-cards');
    }

    public function purchaseGiftCard(Request $request)
    {
        $validated = $request->validate([
            'purchaser_name' => 'required|string|max:255',
            'purchaser_email' => 'required|email|max:255',
            'recipient_name' => 'nullable|string|max:255',
            'recipient_email' => 'nullable|email|max:255',
            'message' => 'nullable|string|max:1000',
            'initial_balance' => 'required|numeric|min:1|max:500',
        ]);

        $service = new GiftCardService();
        $card = $service->create($validated);

        return response()->json([
            'success' => true,
            'gift_card' => [
                'code' => $card->code,
                'balance' => $card->current_balance,
            ],
        ]);
    }

    public function checkGiftCardBalance(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $service = new GiftCardService();
        $card = $service->checkBalance($request->code);

        if (!$card) {
            return response()->json(['success' => false, 'error' => 'Gift card not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'current_balance' => $card->current_balance,
            'expires_at' => $card->expires_at?->format('M j, Y'),
            'is_usable' => $card->isUsable(),
        ]);
    }

    public function gallery()
    {
        $photos = CustomerPhoto::approved()
            ->with('product')
            ->orderByDesc('is_featured')
            ->latest()
            ->paginate(18);

        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('gallery', compact('photos', 'products'));
    }

    public function submitPhoto(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'caption' => 'nullable|string|max:1000',
            'product_id' => 'nullable|exists:products,id',
        ]);

        $path = $request->file('photo')->store('customer-photos', 'public');

        CustomerPhoto::create([
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'caption' => $request->caption,
            'photo_path' => $path,
            'product_id' => $request->product_id,
        ]);

        return redirect()->route('storefront.gallery')
            ->with('success', 'Thank you! Your photo has been submitted and will appear after approval.');
    }
}
