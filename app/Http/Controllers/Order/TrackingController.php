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
    /**
     * Look up orders by customer email.
     */
    public function store(TrackOrderRequest $request, TenantSettings $settings): View
    {
        $orders = Order::query()->forCustomerEmail($request->email)->get();

        $content = settingsPageContent('order_tracking');

        $allStatuses = [
            OrderStatus::Pending->value,
            OrderStatus::Confirmed->value,
            OrderStatus::Baking->value,
            OrderStatus::Ready->value,
            OrderStatus::Delivered->value,
        ];
        $statusLabels = [
            OrderStatus::Pending->value => 'Pending',
            OrderStatus::Confirmed->value => 'Confirmed',
            OrderStatus::Baking->value => 'Baking',
            OrderStatus::Ready->value => 'Ready',
            OrderStatus::Delivered->value => 'Delivered',
        ];

        return view('order-tracking', [
            'settings' => $settings,
            'orders' => $orders,
            'email' => $request->email,
            'content' => $content,
            'allStatuses' => $allStatuses,
            'statusLabels' => $statusLabels,
        ]);
    }

    /**
     * Show the order tracking page.
     */
    public function show(TenantSettings $settings): View
    {
        $content = settingsPageContent('order_tracking');

        return view('order-tracking', [
            'settings' => $settings,
            'content' => $content,
        ]);
    }
}
