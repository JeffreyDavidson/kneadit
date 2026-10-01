<?php

namespace App\Services\Orders;

use App\DataTransferObjects\Settings\SettingValue;
use App\Enums\Orders\DeliveryType;
use App\Exceptions\Orders\OrderNotModifiableException;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;
use App\ValueObjects\Money;

/**
 * Re-prices the discount and gift card portions of an order whose items a
 * customer has edited.
 *
 * Orders store only the aggregate discount and not the delivery tier, so the
 * discount is scaled with the subtotal and delivery is never re-priced. An
 * edit that would break a minimum the order was placed under is rejected.
 */
final readonly class ModifiedOrderPricing
{
    public function __construct(
        private TenantSettings $settings,
    ) {}

    /**
     * Scale the discount placed on the order down with the subtotal, never up.
     * Always derived from the subtotal and discount at placement, so repeated
     * edits cannot drift.
     */
    public function discount(Money $originalDiscount, Money $originalSubtotal, Money $newSubtotal): Money
    {
        if (! $originalSubtotal->isPositive()) {
            return $originalDiscount;
        }

        return $originalDiscount->multiply(
            min(1.0, $newSubtotal->cents() / $originalSubtotal->cents()),
        );
    }

    /**
     * The gift card draw never grows, and is capped at what is left to pay.
     */
    public function giftCardAmount(Money $currentAmount, Money $beforeGiftCard): Money
    {
        return $currentAmount->min($beforeGiftCard);
    }

    /**
     * @throws OrderNotModifiableException when a reduction drops the order below a minimum
     */
    public function assertMinimumsMet(Order $order, Money $previousSubtotal, Money $newSubtotal): void
    {
        // Raising or keeping the subtotal can never be what breaks a minimum.
        if (! $previousSubtotal->greaterThan($newSubtotal)) {
            return;
        }

        $orders = $this->settings->orders;
        $isDelivery = $order->delivery_type === DeliveryType::Delivery;

        $minimum = Money::fromDollars(SettingValue::float(
            $isDelivery ? $orders->minimumDeliveryOrderAmount : $orders->minimumPickupOrderAmount,
            0.0,
        ));

        throw_if(
            $minimum->isPositive() && $minimum->greaterThan($newSubtotal),
            OrderNotModifiableException::class,
            $order,
            "This change would drop your order below the {$minimum->formatted()} minimum.",
        );

        $freeDeliveryMinimum = Money::fromDollars(SettingValue::float($orders->freeDeliveryMinimum, 0.0));

        // A $0 fee only proves the order qualified for free delivery when it
        // was at or above the minimum; below it, the fee came from a $0 tier.
        $qualifiedForFreeDelivery = $isDelivery
            && $order->delivery_fee->isZero()
            && $freeDeliveryMinimum->isPositive()
            && ! $freeDeliveryMinimum->greaterThan($previousSubtotal);

        throw_if(
            $qualifiedForFreeDelivery && $freeDeliveryMinimum->greaterThan($newSubtotal),
            OrderNotModifiableException::class,
            $order,
            "This change would drop your order below the {$freeDeliveryMinimum->formatted()} free delivery minimum.",
        );
    }
}
