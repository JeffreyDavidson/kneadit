<?php

namespace App\Http\Controllers\Order;

use App\Enums\Orders\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\TrackOrderRequest;
use App\Models\Orders\Order;
use App\Presenters\OrderTrackingPresenter;
use App\Services\Orders\OrderAccessGuard;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;

class TrackingController extends Controller
{
    public function store(TrackOrderRequest $request, TenantSettings $settings): View
    {
        $email = $request->string('email')->toString();
        $orders = Order::query()->forCustomerEmail($email)->get();
        $trackableStatuses = OrderStatus::trackableStatuses();

        // Successful email lookup proves ownership of every returned order
        // for the duration of this session.
        $orders->each(fn (Order $order) => OrderAccessGuard::grant($order));

        return view('storefront.order-tracking', [
            'settings' => $settings,
            'storefrontTheme' => (string) settings('storefront_theme', 'classic'),
            'orders' => $orders,
            'email' => $email,
            'content' => settingsPageContent('order_tracking'),
            'trackableStatuses' => $trackableStatuses,
            'trackedOrders' => $orders->map(fn (Order $o) => OrderTrackingPresenter::for($o)),
        ]);
    }

    public function show(TenantSettings $settings): View
    {
        return view('storefront.order-tracking', [
            'settings' => $settings,
            'storefrontTheme' => (string) settings('storefront_theme', 'classic'),
            'content' => settingsPageContent('order_tracking'),
        ]);
    }
}
