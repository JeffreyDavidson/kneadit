<?php

namespace App\Http\Controllers\Storefront\Account;

use App\Http\Controllers\Controller;
use App\Models\Customers\Customer;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;

class OrderHistoryController extends Controller
{
    public function __invoke(TenantSettings $settings): View
    {
        /** @var Customer $customer */
        $customer = auth('customer')->user();

        $orders = $customer->orders()
            ->with('orderItems.product')
            ->latest()
            ->paginate(20);

        return view('storefront.account.orders', [
            'customer' => $customer,
            'orders' => $orders,
            'settings' => $settings,
        ]);
    }
}
