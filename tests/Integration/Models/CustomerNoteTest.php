<?php

use App\Models\Customers\Customer;
use App\Models\Customers\CustomerNote;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('created by relationship returns the user who created the note', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    $note = CustomerNote::factory()
        ->recycle($customer)
        ->recycle($user)
        ->create();

    expect($note->createdBy->id)->toBe($user->id);
});

test('customer and created by are fillable', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    $note = CustomerNote::factory()
        ->recycle($customer)
        ->recycle($user)
        ->create(['note' => 'Test note content']);

    expect($note->note)->toBe('Test note content')
        ->and($note->customer_id)->toBe($customer->id)
        ->and($note->created_by)->toBe($user->id);
});
