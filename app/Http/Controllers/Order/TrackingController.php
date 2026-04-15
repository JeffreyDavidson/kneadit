<?php

namespace App\Http\Controllers\Order;

use App\Enums\Orders\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\TrackOrderRequest;
use App\Models\Orders\Order;
use App\Presenters\OrderTrackingPresenter;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;

class TrackingController extends Controller
{
    public function store(TrackOrderRequest $request, TenantSettings $settings): View
    {
        $orders = Order::query()->forCustomerEmail($request->email)->get();
        $trackableStatuses = OrderStatus::trackableStatuses();

        return view('storefront.order-tracking', [
            'settings' => $settings,
            'orders' => $orders,
            'email' => $request->email,
            'content' => settingsPageContent('order_tracking'),
            'trackableStatuses' => $trackableStatuses,
            'trackedOrders' => $orders->map(fn (Order $o) => new OrderTrackingPresenter($o, $trackableStatuses)),
        ]);
    }

    public function show(TenantSettings $settings): View
    {
        return view('storefront.order-tracking', [
            'settings' => $settings,
            'content' => settingsPageContent('order_tracking'),
        ]);
    }
}
