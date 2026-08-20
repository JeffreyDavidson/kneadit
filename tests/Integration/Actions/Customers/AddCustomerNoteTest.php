<?php

use App\Actions\Customers\AddCustomerNote;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerNote;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('creates a customer note', function () {
    $user = User::factory()->owner()->create();
    $customer = Customer::factory()->create();

    resolve(AddCustomerNote::class)($customer->id, 'Great customer!', $user->id);

    expect(CustomerNote::query()->where('customer_id', $customer->id)->firstOrFail())
        ->not->toBeNull()
        ->note->toBe('Great customer!')
        ->created_by->toBe($user->id);
});
