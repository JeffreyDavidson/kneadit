<?php

namespace App\Exceptions\Orders;

use Carbon\Carbon;
use Illuminate\Contracts\Debug\ShouldntReport;
use RuntimeException;

class CapacityExceededException extends RuntimeException implements ShouldntReport
{
    public function __construct(
        public readonly Carbon $date,
        public readonly int $maxOrders,
    ) {
        parent::__construct("Capacity exceeded for {$date->toDateString()} (max: {$maxOrders})");
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return [
            'date' => $this->date->toDateString(),
            'max_orders' => $this->maxOrders,
        ];
    }
}
