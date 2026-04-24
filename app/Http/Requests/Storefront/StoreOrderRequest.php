<?php

namespace App\Http\Requests\Storefront;

use App\DataTransferObjects\Orders\CreateOrderData;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
            'delivery_type' => ['required', 'in:pickup,delivery'],
            'delivery_address' => ['required_if:delivery_type,delivery', 'nullable', 'string', 'max:500'],
            'delivery_date' => ['required', 'date', 'after_or_equal:' . now()->addDays(resolve(TenantSettings::class)->leadTimeDays())->toDateString()],
            'delivery_time' => ['nullable', 'string', 'max:20'],
            'delivery_tier' => ['required_if:delivery_type,delivery', 'nullable', 'in:under5,5to10,10to15,over15'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'coupon_id' => ['nullable', 'integer', 'exists:coupons,id'],
            'gift_card_id' => ['nullable', 'integer', 'exists:gift_cards,id'],
            'tip_amount' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'pickup_contact_name' => ['nullable', 'string', 'max:255'],
            'pickup_contact_phone' => ['nullable', 'string', 'max:20'],
            'pickup_contact_email' => ['nullable', 'email', 'max:255'],
        ];
    }

    public function toData(): CreateOrderData
    {
        return CreateOrderData::fromArray($this->validated());
    }
}
