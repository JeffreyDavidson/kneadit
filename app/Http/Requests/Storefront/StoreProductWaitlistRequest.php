<?php

declare(strict_types=1);

namespace App\Http\Requests\Storefront;

use App\Models\Inventory\Product;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductWaitlistRequest extends FormRequest
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
            'product_id' => [
                'required',
                'exists:products,id',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (Product::query()->whereKey($value)->where('is_active', true)->exists()) {
                        $fail('This item is available now.');
                    }
                },
            ],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
