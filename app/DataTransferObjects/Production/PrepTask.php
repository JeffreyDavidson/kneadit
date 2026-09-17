<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Production;

use Illuminate\Support\Carbon;

final readonly class PrepTask
{
    public function __construct(
        public string $date,
        public string $orderNumber,
        public string $customerName,
        public string $productName,
        public string $recipeName,
        public int $quantity,
        public int $prepTimeMinutes,
        public string $deliveryTime,
        public string $prepStartTime,
        public Carbon $prepStartDateTime,
    ) {}
}
