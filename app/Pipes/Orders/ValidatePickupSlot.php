<?php

namespace App\Pipes\Orders;

use App\Enums\Orders\DeliveryType;
use App\Exceptions\Orders\PickupSlotUnavailableException;
use App\Services\Scheduling\PickupSlotResolver;
use App\Services\Settings\TenantSettings;
use Closure;

/**
 * Re-checks the chosen pickup slot inside the order transaction. The request
 * rule validates against the slots as they were a moment earlier; this pipe
 * recounts the slot's active orders with the rows locked, so two submissions
 * for the last spot can't both succeed.
 */
class ValidatePickupSlot
{
    public function __construct(
        private readonly TenantSettings $settings,
        private readonly PickupSlotResolver $slots,
    ) {}

    public function handle(OrderPipelineData $payload, Closure $next): mixed
    {
        $data = $payload->data;

        if (
            ! $this->settings->orders->pickupSlotsEnabled
            || $data->deliveryType !== DeliveryType::Pickup->value
            || $data->deliveryTime === null
        ) {
            return $next($payload);
        }

        if ($this->slots->isFull($data->deliveryDate, $data->deliveryTime, lockForUpdate: true)) {
            throw new PickupSlotUnavailableException($data->deliveryDate, $data->deliveryTime);
        }

        return $next($payload);
    }
}
