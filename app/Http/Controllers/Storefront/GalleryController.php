<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGalleryPhotoRequest;
use App\Models\CustomerPhoto;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function show(): View
    {
        $photos = CustomerPhoto::approved()
            ->with('product')
            ->orderByDesc('is_featured')
            ->latest()
            ->paginate(18);

        $products = Product::query()->active()->orderBy('name')->get();

        $storeName = settings('store_name', 'Our Bakery');
        $heroImage = settings('hero_image');
        $heroImageUrl = $heroImage ? Storage::url($heroImage) : 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1920&q=80';
        $content = settingsPageContent('gallery');

        return view('gallery', compact('photos', 'products', 'storeName', 'heroImageUrl', 'content'));
    }

    public function store(StoreGalleryPhotoRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $path = $request->file('photo')->store('customer-photos', 'public');

        CustomerPhoto::query()->create([
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'],
            'caption' => $validated['caption'] ?? null,
            'photo_path' => $path,
            'product_id' => $validated['product_id'] ?? null,
        ]);

        return to_route('storefront.gallery')
            ->with('success', 'Thank you! Your photo has been submitted and will appear after approval.');
    }
}
