<?php

namespace App\Rules;

use App\Enums\Orders\DeliveryType;
use App\Exceptions\Orders\PickupSlotUnavailableException;
use App\Services\Scheduling\PickupSlotResolver;
use App\Services\Settings\TenantSettings;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Date;
use Throwable;

/**
 * Applied to `delivery_time`: when pickup slots are enabled, a pickup order
 * must name one of the slots that still has room on its delivery date.
 * Implicit, so a missing time fails too.
 */
class PickupSlotAvailable implements DataAwareRule, ValidationRule
{
    public bool $implicit = true;

    /** @var array<string, mixed> */
    protected array $data = [];

    /**
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! resolve(TenantSettings::class)->orders->pickupSlotsEnabled) {
            return;
        }

        if (($this->data['delivery_type'] ?? null) !== DeliveryType::Pickup->value) {
            return;
        }

        $date = $this->deliveryDate();

        if ($date === null) {
            return;
        }

        if (is_string($value) && in_array($value, resolve(PickupSlotResolver::class)->availableSlots($date), true)) {
            return;
        }

        $fail(PickupSlotUnavailableException::CUSTOMER_MESSAGE);
    }

    private function deliveryDate(): ?string
    {
        $value = $this->data['delivery_date'] ?? null;

        if (! is_string($value)) {
            return null;
        }

        try {
            return Date::parse($value)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }
}
