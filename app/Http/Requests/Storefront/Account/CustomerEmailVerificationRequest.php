<?php

namespace App\Http\Requests\Storefront\Account;

use App\Models\Customers\Customer;
use App\Support\DatabaseValue;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;

class CustomerEmailVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $customer = $this->user('customer');

        if (! $customer instanceof Customer) {
            return false;
        }

        $routeId = DatabaseValue::nullableString($this->route('id')) ?? '';
        $routeHash = DatabaseValue::nullableString($this->route('hash')) ?? '';

        throw_unless(hash_equals(DatabaseValue::scalarString($customer->getKey()), $routeId), AuthorizationException::class);

        return hash_equals(hash('sha256', $customer->getEmailForVerification()), $routeHash);
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [];
    }
}
