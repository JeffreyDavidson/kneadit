<?php

namespace App\Pipes\Orders;

use App\Enums\Orders\DeliveryType;
use App\Exceptions\Orders\MinimumOrderAmountNotMetException;
use App\Services\Settings\TenantSettings;
use App\ValueObjects\Money;
use Closure;

class EnforceMinimumOrderAmount
{
    public function __construct(
        private TenantSettings $settings,
    ) {}

    public function handle(OrderPipelineData $payload, Closure $next): mixed
    {
        $isDelivery = $payload->data->deliveryType === DeliveryType::Delivery->value;
        $orders = $this->settings->orders;

        $minimum = (float) ($isDelivery
            ? $orders->minimumDeliveryOrderAmount
            : $orders->minimumPickupOrderAmount);
        $minimumMoney = Money::fromDollars($minimum);

        if ($minimumMoney->isPositive() && $minimumMoney->greaterThan($payload->subtotal)) {
            throw new MinimumOrderAmountNotMetException(
                deliveryType: $payload->data->deliveryType,
                subtotal: $payload->subtotal->dollars(),
                minimum: $minimum,
            );
        }

        return $next($payload);
    }
}
