<?php

namespace App\Pipes\Orders;

use App\DataTransferObjects\Orders\CreateOrderData;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\ValueObjects\Money;

class OrderPipelineData
{
    public Money $subtotal;

    public Money $deliveryFee;

    public Money $discountAmount;

    public Money $couponDiscount;

    public Money $tipAmount;

    public Money $total;

    public Money $sitewideSaleDiscount;

    public Money $referralDiscount;

    public ?int $couponId = null;

    public ?int $giftCardId = null;

    public Money $giftCardAmount;

    public ?Customer $customer = null;

    public ?Customer $referrer = null;

    public ?Order $order = null;

    /** @var array<int, array<string, mixed>> */
    public array $orderItems = [];

    public bool $cancelled = false;

    public function __construct(
        public readonly CreateOrderData $data,
    ) {
        $this->subtotal = Money::zero();
        $this->deliveryFee = Money::zero();
        $this->discountAmount = Money::zero();
        $this->couponDiscount = Money::zero();
        $this->tipAmount = Money::zero();
        $this->total = Money::zero();
        $this->sitewideSaleDiscount = Money::zero();
        $this->referralDiscount = Money::zero();
        $this->giftCardAmount = Money::zero();
    }

    public function recalculateDiscountAmount(): void
    {
        $this->discountAmount = $this->sitewideSaleDiscount
            ->add($this->couponDiscount)
            ->add($this->referralDiscount);
    }

    public function recalculateTotal(): void
    {
        $beforeGiftCard = $this->subtotal
            ->add($this->deliveryFee)
            ->subtract($this->discountAmount)
            ->max(Money::zero());

        $this->total = $beforeGiftCard
            ->subtract($this->giftCardAmount)
            ->max(Money::zero())
            ->add($this->tipAmount);
    }
}
