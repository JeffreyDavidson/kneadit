<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\CustomerPhoto;
use App\Models\Product;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
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
            'customer_name' => strip_tags($request->customer_name),
            'customer_email' => $request->customer_email,
            'caption' => $request->caption ? strip_tags($request->caption) : null,
            'photo_path' => $path,
            'product_id' => $request->product_id,
        ]);

        return redirect()->route('storefront.gallery')
            ->with('success', 'Thank you! Your photo has been submitted and will appear after approval.');
    }
}
