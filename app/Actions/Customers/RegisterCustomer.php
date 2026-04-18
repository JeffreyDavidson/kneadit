<?php

namespace App\Actions\Customers;

use App\Models\Customers\Customer;

class RegisterCustomer
{
    /**
     * Register a new customer, or claim an existing guest customer record
     * (matched by email, no password yet). In the claim case any pre-existing
     * orders stay linked via customer_id and surface once the email is verified.
     *
     * @param array<string, mixed> $data
     */
    public function __invoke(array $data): Customer
    {
        $customer = Customer::query()->firstOrNew(['email' => $data['email']]);

        $customer->fill([
            'name' => $data['name'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? $customer->phone,
        ]);

        // Treat this as a fresh identity — any previous verification was against
        // the guest-order state, not this password-owner.
        $customer->email_verified_at = null;
        $customer->save();

        return $customer;
    }
}
