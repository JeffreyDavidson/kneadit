<?php

namespace App\Exceptions\Orders;

use App\Models\Orders\Order;
use Illuminate\Contracts\Debug\ShouldntReport;
use RuntimeException;

class InsufficientStockException extends RuntimeException implements ShouldntReport
{
    /**
     * @param array<int, string> $shortages Names of ingredients whose projected
     *                                      demand would exceed current stock.
     */
    public function __construct(
        public readonly Order $order,
        public readonly array $shortages,
    ) {
        $list = implode(', ', $shortages);
        parent::__construct("Order {$order->order_number} cannot be modified: insufficient stock for {$list}");
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'shortages' => $this->shortages,
        ];
    }
}
