<?php

use App\Models\Staff\User;
use App\Queries\Platform\StripeCustomerLookupQuery;

beforeEach(fn () => setUpCentralTest());

test('returns null user and tenant when stripe customer not found', function () {
    $result = StripeCustomerLookupQuery::find('cus_nonexistent');

    expect($result['user'])->toBeNull()
        ->and($result['tenant'])->toBeNull();
});

test('returns user when stripe customer is found', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_test_123']);

    $result = StripeCustomerLookupQuery::find('cus_test_123');

    expect($result['user']?->is($user))->toBeTrue();
});
