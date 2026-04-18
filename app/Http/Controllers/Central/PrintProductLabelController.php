<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Product;
use App\Presenters\ProductLabelPresenter;
use App\Services\Settings\TenantSettings;
use Illuminate\View\View;

class PrintProductLabelController extends Controller
{
    public function __invoke(Product $product, TenantSettings $settings): View
    {
        return view('admin.products.label', [
            'product' => $product,
            'settings' => $settings,
            'label' => ProductLabelPresenter::for($product),
        ]);
    }
}
