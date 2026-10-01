<?php

namespace App\Actions\Customers;

use App\Models\Customers\Customer;

class RegisterCustomer
{
    /**
     * Register a new customer, or claim an existing guest customer record
     * (matched by email, no password yet). In the claim case the existing name
     * and phone are kept (a blank phone or name is filled from the submitted
     * data), the password is set, and any pre-existing orders stay linked via
     * customer_id. Order history and order pages are only reachable once the
     * email is verified (see EnsureCustomerEmailIsVerified and OrderAccessGuard).
     *
     * @param  array<string, mixed>  $data
     */
    public function __invoke(array $data): Customer
    {
        $customer = Customer::query()->firstOrNew(['email' => $data['email']]);

        $customer->fill([
            'name' => filled($customer->name) ? $customer->name : $data['name'],
            'password' => $data['password'],
            'phone' => filled($customer->phone) ? $customer->phone : ($data['phone'] ?? null),
        ]);

        // Treat this as a fresh identity — any previous verification was against
        // the guest-order state, not this password-owner.
        $customer->email_verified_at = null;
        $customer->save();

        return $customer;
    }
}
