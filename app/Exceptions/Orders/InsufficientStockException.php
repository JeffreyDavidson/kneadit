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
     * @param ?Order $order Set when the failure attaches to a persisted order
     *                      (the modification path); null at placement time
     *                      where the order doesn't exist yet.
     */
    public function __construct(
        public readonly array $shortages,
        public readonly ?Order $order = null,
    ) {
        $list = implode(', ', $shortages);

        $prefix = $order !== null
            ? "Order {$order->order_number} cannot be modified"
            : 'Order cannot be placed';

        parent::__construct("{$prefix}: insufficient stock for {$list}");
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return [
            'order_id' => $this->order?->id,
            'order_number' => $this->order?->order_number,
            'shortages' => $this->shortages,
        ];
    }
}
