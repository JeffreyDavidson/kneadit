<?php

use App\Actions\Customers\GenerateCustomerReferralCode;
use App\Models\Customers\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('generates and persists a referral code on a customer that has none', function () {
    $customer = Customer::factory()->create(['referral_code' => null]);

    $code = resolve(GenerateCustomerReferralCode::class)($customer);

    expect($code)->toMatch('/^[A-Z0-9]{8}$/')
        ->and($customer->fresh()->referral_code)->toBe($code);
});

test('returns the existing code without regenerating', function () {
    $customer = Customer::factory()->create(['referral_code' => 'KEEPME01']);

    $code = resolve(GenerateCustomerReferralCode::class)($customer);

    expect($code)->toBe('KEEPME01');
});

test('observer auto-generates a code for newly-created customers', function () {
    $customer = Customer::factory()->create(['referral_code' => null]);

    expect($customer->fresh()->referral_code)->toMatch('/^[A-Z0-9]{8}$/');
});
