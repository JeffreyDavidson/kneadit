<?php

namespace App\Http\Controllers\Storefront\Account;

use App\Http\Controllers\Controller;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerFavorite;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;

class CustomerDashboardController extends Controller
{
    public function __invoke(TenantSettings $settings): View
    {
        /** @var Customer $customer */
        $customer = auth('customer')->user();

        $customer->loadMissing([
            'orders' => fn ($q) => $q->latest()->limit(10),
            'orders.orderItems.product',
        ]);

        $favorites = CustomerFavorite::query()
            ->forCustomer($customer->email)
            ->with('product')
            ->latest()
            ->get();

        return view('storefront.account.dashboard', [
            'customer' => $customer,
            'orders' => $customer->orders,
            'favorites' => $favorites,
            'settings' => $settings,
        ]);
    }
}
