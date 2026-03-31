<?php

use App\Actions\Customers\AddCustomerNote;
use App\Models\Customer;
use App\Models\CustomerNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('creates a customer note', function () {
    $user = User::factory()->owner()->create();
    $customer = Customer::factory()->create();

    resolve(AddCustomerNote::class)($customer->id, 'Great customer!', $user->id);

    expect(CustomerNote::query()->where('customer_id', $customer->id)->first())
        ->not->toBeNull()
        ->note->toBe('Great customer!')
        ->created_by->toBe($user->id);
});
