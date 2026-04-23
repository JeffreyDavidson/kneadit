<?php

namespace App\Pipes\Orders;

use App\DataTransferObjects\Orders\CreateOrderData;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;

class OrderPipelineData
{
    public float $subtotal = 0;

    public float $deliveryFee = 0;

    public float $discountAmount = 0;

    public float $tipAmount = 0;

    public float $total = 0;

    public ?int $couponId = null;

    public ?int $giftCardId = null;

    public float $giftCardAmount = 0;

    public ?Customer $customer = null;

    public ?Order $order = null;

    /** @var array<int, array<string, mixed>> */
    public array $orderItems = [];

    public bool $cancelled = false;

    public function __construct(
        public readonly CreateOrderData $data,
    ) {}
}
