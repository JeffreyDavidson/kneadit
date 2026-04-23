<?php

namespace App\Exceptions\Orders;

use App\Models\Orders\Order;
use Illuminate\Contracts\Debug\ShouldntReport;
use RuntimeException;

class OrderNotModifiableException extends RuntimeException implements ShouldntReport
{
    public function __construct(
        public readonly Order $order,
        public readonly string $reason,
    ) {
        parent::__construct("Order {$order->order_number} cannot be modified: {$reason}");
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'reason' => $this->reason,
        ];
    }
}
