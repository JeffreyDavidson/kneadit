<?php

namespace App\Actions\Orders;

use App\Models\Orders\Order;

class RecordPayPalInvoice
{
    public function __invoke(Order $order, string $invoiceId): void
    {
        $order->update(['paypal_invoice_id' => $invoiceId]);
    }
}
