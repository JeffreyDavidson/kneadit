<?php

namespace App\Http\Requests\Storefront;

use App\DataTransferObjects\Orders\CreateOrderData;
use App\Enums\Orders\DeliveryType;
use App\Models\Orders\OrderItem;
use App\Rules\PickupSlotAvailable;
use App\Rules\ProductAvailableOnDeliveryDate;
use App\Services\Scheduling\EarliestDeliveryDate;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'customer_birthday' => ['nullable', 'date'],
            'delivery_type' => ['required', Rule::in($this->allowedDeliveryTypes())],
            'delivery_address' => ['required_if:delivery_type,delivery', 'nullable', 'string', 'max:500'],
            'delivery_date' => ['required', 'date', 'after_or_equal:'.resolve(EarliestDeliveryDate::class)->get()->toDateString()],
            'delivery_time' => ['nullable', 'string', 'max:20', new PickupSlotAvailable],
            'delivery_tier' => ['required_if:delivery_type,delivery', 'nullable', Rule::in(resolve(TenantSettings::class)->orders->deliveryTierKeys())],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id', new ProductAvailableOnDeliveryDate],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:'.OrderItem::MAX_QUANTITY],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'gift_card_id' => [
                'nullable',
                'integer',
                Rule::exists('gift_cards', 'id')->where(fn (Builder $query) => $query->where('code', $this->input('gift_card_code'))),
            ],
            'gift_card_code' => ['required_with:gift_card_id', 'nullable', 'string', 'max:50'],
            'tip_amount' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'pickup_contact_name' => ['nullable', 'string', 'max:255'],
            'pickup_contact_phone' => ['nullable', 'string', 'max:20'],
            'pickup_contact_email' => ['nullable', 'email', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.*.quantity.max' => sprintf(
                'You can order up to %d of one item. Contact us for larger orders.',
                OrderItem::MAX_QUANTITY,
            ),
        ];
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
