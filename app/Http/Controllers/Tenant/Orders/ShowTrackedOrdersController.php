<?php

namespace App\Http\Controllers\Tenant\Orders;

use App\Enums\Orders\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Presenters\OrderTrackingPresenter;
use App\Services\Orders\OrderAccessGuard;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;

/**
 * Landing page for the signed link emailed by the tracking form. Opening the
 * link proves control of the customer's inbox, so it unlocks the customer's
 * orders for this session.
 */
class ShowTrackedOrdersController extends Controller
{
    public function __invoke(Customer $customer, TenantSettings $settings): View
    {
        $orders = Order::query()->forCustomerEmail($customer->email)->get();

        $orders->each(fn (Order $order) => OrderAccessGuard::grant($order));

        return view('tenant.storefront.order-tracking', [
            'settings' => $settings,
            'storefrontTheme' => $settings->branding->storefrontTheme,
            'orders' => $orders,
            'email' => $customer->email,
            'content' => settingsPageContent('order_tracking'),
            'trackableStatuses' => OrderStatus::trackableStatuses(),
            'trackedOrders' => $orders->map(fn (Order $o): OrderTrackingPresenter => OrderTrackingPresenter::for($o)),
        ]);
    }
}
