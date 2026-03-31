<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Settings\TenantSettings;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __invoke(Order $order, TenantSettings $settings): View
    {
        $order->load(['customer', 'orderItems.product']);

        return view('admin.orders.invoice', [
            'order' => $order,
            'settings' => $settings,
        ]);
    }
}
