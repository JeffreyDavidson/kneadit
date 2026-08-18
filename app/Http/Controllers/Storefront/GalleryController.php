<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Customers\CreateCustomerPhoto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreGalleryPhotoRequest;
use App\Models\Customers\CustomerPhoto;
use App\Models\Inventory\Product;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class GalleryController extends Controller
{
    public function store(StoreGalleryPhotoRequest $request, CreateCustomerPhoto $createPhoto): RedirectResponse
    {
        $createPhoto(
            photo: $request->file('photo'),
            customerName: $request->validated('customer_name'),
            customerEmail: $request->validated('customer_email'),
            caption: $request->validated('caption'),
            productId: $request->validated('product_id'),
        );

        return to_route('storefront.gallery')
            ->with('success', 'Thank you! Your photo has been submitted and will appear after approval.');
    }

    public function show(TenantSettings $settings): View
    {
        return view('storefront.gallery', [
            'settings' => $settings,
            'photos' => CustomerPhoto::query()->approved()
                ->with('product')
                ->orderByDesc('is_featured')
                ->latest()
                ->paginate(18),
            'products' => Product::query()->active()->orderBy('name')->get(['id', 'name']),
            'content' => settingsPageContent('gallery'),
        ]);
    }
}
