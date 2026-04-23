<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Category;
use App\Services\Carts\CartManager;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;

class ShowOrderFormController extends Controller
{
    public function __invoke(TenantSettings $settings, CartManager $cartManager): View
    {
        $categories = Category::query()
            ->active()
            ->withActiveProducts()
            ->orderBy('sort_order')
            ->get();

        $cart = $cartManager->current();
        $hydratedItems = $cart
            ? $cart->items()->with('product')->get()->map(fn ($item) => [
                'id' => $item->product_id,
                'name' => $item->product->name ?? 'Product',
                'price' => $item->unit_price->dollars(),
                'quantity' => $item->quantity,
            ])->values()->all()
            : [];

        return view('storefront.order', [
            'settings' => $settings,
            'categories' => $categories,
            'content' => settingsPageContent('order'),
            'hydratedCartItems' => $hydratedItems,
            'hydratedCartEmail' => $cart?->customer_email,
            'hydratedCartName' => $cart?->customer_name,
        ]);
    }
}
