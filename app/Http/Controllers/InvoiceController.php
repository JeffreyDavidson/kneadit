<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function show(Order $order): View
    {
        // Load necessary relationships
        $order->load(['customer', 'orderItems.product']);

        // Get store information from settings
        $storeInfo = [
            'name' => Setting::get('store_name') ?? config('app.name'),
            'address' => Setting::get('store_address') ?? 'Address not configured',
            'phone' => Setting::get('store_phone') ?? 'Phone not configured',
            'email' => Setting::get('store_email') ?? 'Email not configured',
            'website' => Setting::get('store_website') ?? url('/'),
        ];

        return view('admin.orders.invoice', compact('order', 'storeInfo'));
    }
}
