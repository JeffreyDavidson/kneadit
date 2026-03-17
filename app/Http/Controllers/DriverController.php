<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Setting;
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

        $storeName = Setting::get('store_name', 'Our Bakery');

        return view('driver', compact('orders', 'storeName'));
    }

    public function update(Order $order): RedirectResponse
    {
        $order->update(['status' => OrderStatus::Delivered]);

        return back()->with('success', "Order #{$order->order_number} marked as delivered!");
    }
}
