<?php

namespace App\Actions\Customers;

use App\Models\Customers\Customer;
use Illuminate\Support\Str;

/**
 * Generates a unique referral code for a customer if they don't already have one.
 */
class GenerateCustomerReferralCode
{
    public function __invoke(Customer $customer): string
    {
        if ($customer->referral_code) {
            return $customer->referral_code;
        }

        do {
            $code = strtoupper(Str::random(8));
        } while (Customer::query()->where('referral_code', $code)->exists());

        $customer->forceFill(['referral_code' => $code])->save();

        return $code;
    }
}
