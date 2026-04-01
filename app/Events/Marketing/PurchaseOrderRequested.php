<?php

namespace App\Events\Marketing;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class PurchaseOrderRequested implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    /**
     * @param array<int, array<string, mixed>> $items
     */
    public function __construct(
        public readonly string $supplierEmail,
        public readonly string $supplierName,
        public readonly string $storeName,
        public readonly array $items,
        public readonly float $total,
        public readonly string $requestedDate,
    ) {}
}
