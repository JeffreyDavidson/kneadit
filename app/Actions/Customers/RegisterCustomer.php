<?php

namespace App\Actions\Customers;

use App\Models\Customers\Customer;

class RegisterCustomer
{
    /**
     * @param array<string, mixed> $data
     */
    public function __invoke(array $data): Customer
    {
        return Customer::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
        ]);
    }
}
