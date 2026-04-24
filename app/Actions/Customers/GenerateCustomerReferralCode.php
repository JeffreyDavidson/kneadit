<?php

namespace App\Actions\Customers;

use App\Models\Customers\Customer;
use Illuminate\Support\Str;

/**
 * Generates a unique referral code for a customer if they don't already have one.
 *
 * Works in two contexts:
 * - Pre-insert (called from the `creating` observer hook) — assigns the code
 *   so the upcoming INSERT picks it up. The customer is not yet persisted.
 * - Post-insert (called from the backfill command) — assigns and saves so the
 *   change is persisted.
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

        $customer->referral_code = $code;

        if ($customer->exists) {
            $customer->save();
        }

        return $code;
    }
}
