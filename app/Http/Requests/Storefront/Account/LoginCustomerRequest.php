<?php

declare(strict_types=1);

namespace App\Http\Requests\Storefront\Account;

use App\Support\EmailAddress;
use Illuminate\Foundation\Http\FormRequest;

class LoginCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! is_string($this->input('email'))) {
            return;
        }

        $this->merge(['email' => EmailAddress::normalize($this->input('email'))]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}
