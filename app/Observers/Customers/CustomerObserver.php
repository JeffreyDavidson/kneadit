<?php

namespace App\Observers\Customers;

use App\Actions\Customers\GenerateCustomerReferralCode;
use App\Models\Customers\Customer;

class CustomerObserver
{
    public function created(Customer $customer): void
    {
        if (! $customer->referral_code) {
            resolve(GenerateCustomerReferralCode::class)($customer);
        }
    }
}
