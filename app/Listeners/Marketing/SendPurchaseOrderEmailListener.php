<?php

namespace App\Listeners\Marketing;

use App\Events\Marketing\PurchaseOrderRequested;
use App\Listeners\SendEmailListener;
use App\Mail\Orders\PurchaseOrderMail;
use Illuminate\Contracts\Mail\Mailable;

class SendPurchaseOrderEmailListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        /** @var PurchaseOrderRequested $event */
        return $event->supplierEmail;
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var PurchaseOrderRequested $event */
        return new PurchaseOrderMail(
            supplierName: $event->supplierName,
            storeName: $event->storeName,
            items: $event->items,
            total: $event->total,
            requestedDate: $event->requestedDate,
        );
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var PurchaseOrderRequested $event */
        return ['supplier' => $event->supplierName];
    }
}
