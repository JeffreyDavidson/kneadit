<?php

namespace App\Http\Controllers;

use App\Actions\Orders\TransitionOrderStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Queries\DriverDeliveryQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class DriverController extends Controller
{
    public function index(): View
    {
        $orders = DriverDeliveryQuery::forDate(today());
        $storeName = settings('store_name', 'Our Bakery');

        return view('driver', [
            'orders' => $orders,
            'storeName' => $storeName,
        ]);
    }

    public function update(Order $order, TransitionOrderStatus $transitionStatus): RedirectResponse
    {
        $transitionStatus($order, OrderStatus::Delivered);

        return back()->with('success', "Order #{$order->order_number} marked as delivered!");
    }
}
