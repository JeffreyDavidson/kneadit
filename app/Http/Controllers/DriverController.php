<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;

class DriverController extends Controller
{
    public function index()
    {
        $orders = Order::with(['customer', 'orderItems.product'])
            ->whereNotNull('delivery_address')
            ->where('delivery_address', '!=', '')
            ->whereIn('status', ['confirmed', 'baking', 'ready'])
            ->whereDate('requested_date', today())
            ->orderBy('requested_time')
            ->get();

        $storeName = Setting::get('store_name', 'Our Bakery');

        return view('driver', compact('orders', 'storeName'));
    }

    public function markDelivered(Order $order)
    {
        $order->update(['status' => 'delivered']);

        return back()->with('success', "Order #{$order->order_number} marked as delivered!");
    }
}
