<?php

namespace App\Http\Controllers;

use App\Actions\Orders\TransitionOrderStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class DriverController extends Controller
{
    public function index(): View
    {
        $orders = Order::with(['customer', 'orderItems.product'])
            ->whereNotNull('delivery_address')
            ->where('delivery_address', '!=', '')
            ->whereIn('status', [OrderStatus::Confirmed, OrderStatus::Baking, OrderStatus::Ready])
            ->whereDate('delivery_date', today())
            ->orderBy('delivery_time')
            ->get();

        $storeName = settings('store_name', 'Our Bakery');

        return view('driver', compact('orders', 'storeName'));
    }

    public function update(Order $order, TransitionOrderStatus $transitionStatus): RedirectResponse
    {
        $transitionStatus($order, OrderStatus::Delivered);

        return back()->with('success', "Order #{$order->order_number} marked as delivered!");
    }
}
