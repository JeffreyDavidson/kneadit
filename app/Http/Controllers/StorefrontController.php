<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CustomerFavorite;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    public function home()
    {
        $categories = Category::where('is_active', true)
            ->with(['products' => fn($q) => $q->where('is_active', true)->where('is_featured', true)])
            ->orderBy('sort_order')
            ->get();

        $storeName = Setting::get('store_name', 'Our Bakery');

        return view('storefront.home', compact('categories', 'storeName'));
    }

    public function menu()
    {
        $categories = Category::where('is_active', true)
            ->with(['products' => fn($q) => $q->where('is_active', true)->orderBy('name')])
            ->orderBy('sort_order')
            ->get();

        $storeName = Setting::get('store_name', 'Our Bakery');

        return view('storefront.menu', compact('categories', 'storeName'));
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
}
