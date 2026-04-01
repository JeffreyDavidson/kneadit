<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Orders\Order;
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
