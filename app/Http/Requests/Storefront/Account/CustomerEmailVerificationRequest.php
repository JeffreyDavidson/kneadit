<?php

namespace App\Http\Requests\Storefront\Account;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;

class CustomerEmailVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $customer = $this->user('customer');

        if (! $customer) {
            return false;
        }

        throw_unless(hash_equals((string) $customer->getKey(), (string) $this->route('id')), AuthorizationException::class);

        return hash_equals(sha1($customer->getEmailForVerification()), (string) $this->route('hash'));
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [];
    }
}
