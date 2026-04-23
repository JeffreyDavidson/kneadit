<?php

namespace App\Http\Requests\Api;

use App\DataTransferObjects\Orders\CreateOrderData;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Http\FormRequest;

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
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'items.*.special_instructions' => ['nullable', 'string', 'max:500'],
            'delivery_date' => ['required', 'date', 'after_or_equal:' . $this->minimumDeliveryDate()],
            'delivery_time' => ['nullable', 'string', 'max:20'],
            'delivery_type' => ['required', 'in:pickup,delivery'],
            'delivery_address' => ['required_if:delivery_type,delivery', 'nullable', 'string', 'max:500'],
            'delivery_tier' => ['required_if:delivery_type,delivery', 'nullable', 'in:under5,5to10,10to15,over15'],
            'notes' => ['nullable', 'string', 'max:500'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'tip_amount' => ['nullable', 'numeric', 'min:0', 'max:1000'],
        ];
    }

    private function minimumDeliveryDate(): string
    {
        $leadDays = rescue(fn () => app(TenantSettings::class)->leadTimeDays(), 1, false);

        return now()->addDays($leadDays)->toDateString();
    }

    public function toData(): CreateOrderData
    {
        return CreateOrderData::fromArray($this->validated());
    }
}
