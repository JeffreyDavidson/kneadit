<?php

namespace App\Presenters;

use App\Models\Orders\OrderItem;
use App\ValueObjects\Money;

final class OrderItemPresenter
{
    public function __construct(
        public readonly OrderItem $orderItem,
    ) {}

    public static function for(OrderItem $orderItem): self
    {
        return new self($orderItem);
    }

    public function totalPrice(): Money
    {
        return $this->orderItem->unit_price->multiply($this->orderItem->quantity);
    }
}
