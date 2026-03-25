<?php

namespace App\Http\Requests;

use App\Enums\DeliveryType;
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
            'customer_phone' => ['required', 'string', 'max:50'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.special_instructions' => ['nullable', 'string', 'max:500'],
            'delivery_date' => ['required', 'date', 'after_or_equal:today'],
            'delivery_time' => ['nullable', 'string'],
            'delivery_type' => ['nullable', Rule::in(DeliveryType::cases())],
            'delivery_address' => ['nullable', 'string', 'max:500'],
            'delivery_tier' => ['nullable', 'in:under5,5to10,10to15,over15'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'coupon_code' => ['nullable', 'string'],
        ];
    }
}
