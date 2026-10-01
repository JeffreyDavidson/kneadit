<?php

declare(strict_types=1);

namespace App\Exceptions\Orders;

use Illuminate\Contracts\Debug\ShouldntReport;
use RuntimeException;

class PickupSlotUnavailableException extends RuntimeException implements ShouldntReport
{
    /** What the customer sees when the slot they chose can't be booked. */
    public const string CUSTOMER_MESSAGE = 'That pickup time is no longer available. Please choose another.';

    public function __construct(
        public readonly string $date,
        public readonly string $time,
    ) {
        parent::__construct("Pickup slot {$time} on {$date} is full");
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return [
            'date' => $this->date,
            'time' => $this->time,
        ];
    }
}
