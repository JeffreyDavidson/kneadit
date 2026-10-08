<?php

namespace App\Http\Requests\Api;

use App\DataTransferObjects\Orders\CreateOrderData;
use App\Enums\Orders\DeliveryType;
use App\Rules\PickupSlotAvailable;
use App\Rules\PossiblePhoneNumber;
use App\Rules\ProductAvailableOnDeliveryDate;
use App\Services\Scheduling\EarliestDeliveryDate;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApiOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30', new PossiblePhoneNumber],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id', new ProductAvailableOnDeliveryDate],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'items.*.special_instructions' => ['nullable', 'string', 'max:500'],
            'delivery_date' => ['required', 'date', 'after_or_equal:'.$this->minimumDeliveryDate()],
            'delivery_time' => ['nullable', 'string', 'max:20', new PickupSlotAvailable],
            'delivery_type' => ['required', Rule::in($this->allowedDeliveryTypes())],
            'delivery_address' => ['required_if:delivery_type,delivery', 'nullable', 'string', 'max:500'],
            'delivery_tier' => ['required_if:delivery_type,delivery', 'nullable', Rule::in(resolve(TenantSettings::class)->orders->deliveryTierKeys())],
            'notes' => ['nullable', 'string', 'max:500'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'tip_amount' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'pickup_contact_name' => ['nullable', 'string', 'max:255'],
            'pickup_contact_phone' => ['nullable', 'string', 'max:30', new PossiblePhoneNumber],
            'pickup_contact_email' => ['nullable', 'email', 'max:255'],
        ];
    }

    private function minimumDeliveryDate(): string
    {
        return rescue(
            fn (): string => resolve(EarliestDeliveryDate::class)->get()->toDateString(),
            fn (): string => now()->addDay()->toDateString(),
            false,
        );
    }

    public function toData(): CreateOrderData
    {
        return CreateOrderData::fromArray($this->validated());
    }

    /**
     * Delivery is only offered when the bakery has it turned on.
     *
     * @return list<string>
     */
    private function allowedDeliveryTypes(): array
    {
        return resolve(TenantSettings::class)->orders->deliveryEnabled
            ? [DeliveryType::Pickup->value, DeliveryType::Delivery->value]
            : [DeliveryType::Pickup->value];
    }
}
