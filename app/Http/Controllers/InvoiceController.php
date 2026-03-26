<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function show(Order $order): View
    {
        // Load necessary relationships
        $order->load(['customer', 'orderItems.product']);

        // Get store information from settings
        $storeInfo = [
            'name' => settings('store_name') ?? config('app.name'),
            'address' => settings('store_address') ?? 'Address not configured',
            'phone' => settings('store_phone') ?? 'Phone not configured',
            'email' => settings('store_email') ?? 'Email not configured',
            'website' => settings('store_website') ?? url('/'),
        ];

        return view('admin.orders.invoice', compact('order', 'storeInfo'));
    }
}
