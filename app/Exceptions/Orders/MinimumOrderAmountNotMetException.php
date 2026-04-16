<?php

namespace App\Exceptions\Orders;

use Illuminate\Contracts\Debug\ShouldntReport;
use RuntimeException;

class MinimumOrderAmountNotMetException extends RuntimeException implements ShouldntReport
{
    public function __construct(
        public readonly string $deliveryType,
        public readonly float $subtotal,
        public readonly float $minimum,
    ) {
        parent::__construct(sprintf(
            'Minimum %s order is $%.2f (subtotal: $%.2f)',
            $deliveryType,
            $minimum,
            $subtotal,
        ));
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return [
            'delivery_type' => $this->deliveryType,
            'subtotal' => $this->subtotal,
            'minimum' => $this->minimum,
        ];
    }
}
