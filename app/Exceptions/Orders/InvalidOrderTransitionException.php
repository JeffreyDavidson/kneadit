<?php

namespace App\Exceptions\Orders;

use App\Enums\Orders\OrderStatus;
use App\Models\Orders\Order;
use Illuminate\Contracts\Debug\ShouldntReport;
use RuntimeException;

class InvalidOrderTransitionException extends RuntimeException implements ShouldntReport
{
    public function __construct(
        public readonly Order $order,
        public readonly OrderStatus $from,
        public readonly OrderStatus $to,
    ) {
        parent::__construct("Cannot change status from {$from->value} to {$to->value}");
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'from' => $this->from->value,
            'to' => $this->to->value,
        ];
    }
}
