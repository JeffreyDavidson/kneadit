<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGalleryPhotoRequest;
use App\Models\CustomerPhoto;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class GalleryController extends Controller
{
    public function show(): View
    {
        $photos = CustomerPhoto::approved()
            ->with('product')
            ->orderByDesc('is_featured')
            ->latest()
            ->paginate(18);

        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('gallery', compact('photos', 'products'));
    }

    public function store(StoreGalleryPhotoRequest $request): RedirectResponse
    {

        $path = $request->file('photo')->store('customer-photos', 'public');

        CustomerPhoto::create([
            'customer_name' => strip_tags($request->customer_name),
            'customer_email' => $request->customer_email,
            'caption' => $request->caption ? strip_tags($request->caption) : null,
            'photo_path' => $path,
            'product_id' => $request->product_id,
        ]);

        return to_route('storefront.gallery')
            ->with('success', 'Thank you! Your photo has been submitted and will appear after approval.');
    }
}
