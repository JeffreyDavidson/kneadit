<?php

namespace App\Http\Controllers\Order;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\TrackOrderRequest;
use App\Models\Order;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;

class TrackingController extends Controller
{
    public function store(TrackOrderRequest $request, TenantSettings $settings): View
    {
        $orders = Order::query()->forCustomerEmail($request->email)->get();

        return view('order-tracking', [
            'settings' => $settings,
            'orders' => $orders,
            'email' => $request->email,
            'content' => settingsPageContent('order_tracking'),
            'trackableStatuses' => OrderStatus::trackableStatuses(),
        ]);
    }

    public function show(TenantSettings $settings): View
    {
        return view('order-tracking', [
            'settings' => $settings,
            'content' => settingsPageContent('order_tracking'),
        ]);
    }
}
