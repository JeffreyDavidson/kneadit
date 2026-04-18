<?php

namespace App\Http\Requests\Storefront\Account;

use App\Models\Customers\Customer;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                // Allow an email that already belongs to a guest customer record
                // (no password yet) — registration in that case claims the record.
                // Block emails that already have a password.
                function (string $attribute, mixed $value, Closure $fail): void {
                    $existing = Customer::query()->where('email', $value)->first();

                    if ($existing && $existing->password !== null) {
                        $fail('An account with this email already exists. Try signing in instead.');
                    }
                },
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }
}
