<?php

namespace App\Http\Requests\Storefront\Account;

use App\Models\Customers\Customer;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class CustomerEmailVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $customer = $this->user('customer');

        if (! $customer instanceof Customer) {
            return false;
        }

        $route = Validator::make([
            'id' => $this->route('id'),
            'hash' => $this->route('hash'),
        ], [
            'id' => ['required', 'string'],
            'hash' => ['required', 'string'],
        ])->safe();

        throw_unless(hash_equals((string) $customer->id, $route->string('id')->toString()), AuthorizationException::class);

        return hash_equals(sha1($customer->getEmailForVerification()), $route->string('hash')->toString());
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [];
    }
}
