<?php

namespace App\Observers\Customers;

use App\Actions\Customers\EraseCustomerPersonalRecords;
use App\Actions\Customers\GenerateCustomerReferralCode;
use App\Models\Customers\Customer;

class CustomerObserver
{
    // Generate the referral code BEFORE insert so it lands in the same row —
    // doing it in `created` triggers a second audit-log row (Updated) that
    // looks to reviewers like a real mutation of an existing customer.
    public function creating(Customer $customer): void
    {
        if (! $customer->referral_code) {
            resolve(GenerateCustomerReferralCode::class)($customer);
        }
    }

    // Records keyed on the email (favorites, waitlists, carts and so on) have no
    // foreign key to cascade from, so they are cleaned up here.
    public function deleted(Customer $customer): void
    {
        resolve(EraseCustomerPersonalRecords::class)($customer->id, $customer->email);
    }
}
