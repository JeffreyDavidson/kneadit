<?php

namespace App\Pipes\Orders;

use App\Enums\Orders\DeliveryType;
use App\Exceptions\Orders\MinimumOrderAmountNotMetException;
use App\Services\Settings\TenantSettings;
use Closure;

class EnforceMinimumOrderAmount
{
    public function __construct(
        private TenantSettings $settings,
    ) {}

    public function handle(OrderPipelineData $payload, Closure $next): mixed
    {
        $isDelivery = $payload->data->deliveryType === DeliveryType::Delivery->value;

        $minimum = (float) ($isDelivery
            ? $this->settings->minimumDeliveryOrderAmount
            : $this->settings->minimumPickupOrderAmount);

        if ($minimum > 0 && $payload->subtotal < $minimum) {
            throw new MinimumOrderAmountNotMetException(
                deliveryType: $payload->data->deliveryType,
                subtotal: $payload->subtotal,
                minimum: $minimum,
            );
        }

        return $next($payload);
    }
}
