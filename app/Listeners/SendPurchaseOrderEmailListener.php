<?php

namespace App\Listeners;

use App\Events\PurchaseOrderRequested;
use App\Mail\PurchaseOrderMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPurchaseOrderEmailListener implements ShouldQueue
{
    public int $timeout = 60;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

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
