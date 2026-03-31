<?php

namespace App\Listeners;

use App\Events\PurchaseOrderRequested;
use App\Mail\PurchaseOrderMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPurchaseOrderEmailListener extends QueuedListener
{
    public function handle(PurchaseOrderRequested $event): void
    {
        Mail::to($event->supplierEmail)->send(new PurchaseOrderMail(
            supplierName: $event->supplierName,
            storeName: $event->storeName,
            items: $event->items,
            total: $event->total,
            requestedDate: $event->requestedDate,
        ));
    }

    public function failed(PurchaseOrderRequested $event, \Throwable $exception): void
    {
        Log::warning('Purchase order email failed', [
            'supplier' => $event->supplierName,
            'error' => $exception->getMessage(),
        ]);
    }
}
