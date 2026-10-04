<?php

use App\Models\Staff\User;
use App\Queries\Platform\StripeCustomerLookupQuery;

beforeEach(fn () => setUpCentralTest());

test('returns null user and tenant when stripe customer not found', function () {
    $result = resolve(StripeCustomerLookupQuery::class)->find('cus_nonexistent');

    expect($result['user'])->toBeNull()
        ->and($result['tenant'])->toBeNull();
});

test('returns user when stripe customer is found', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_test_123']);

    $result = resolve(StripeCustomerLookupQuery::class)->find('cus_test_123');

    expect($result['user'])->not->toBeNull()
        ->and($result['user']->id)->toBe($user->id);
});

test('returns the bakery the user owns, even after they changed their email', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_owner', 'email' => 'new-address@example.com']);
    createTenant(['id' => 'owned-bakery', 'email' => 'old-address@example.com', 'user_id' => $user->id]);

    $result = resolve(StripeCustomerLookupQuery::class)->find('cus_owner');

    expect($result['tenant']?->id)->toBe('owned-bakery');
});

test('returns no bakery when the user owns none, even if a bakery shares their email', function () {
    User::factory()->create(['stripe_id' => 'cus_no_bakery', 'email' => 'baker@example.com']);
    createTenant(['id' => 'unowned-bakery', 'email' => 'baker@example.com', 'user_id' => null]);

    $result = resolve(StripeCustomerLookupQuery::class)->find('cus_no_bakery');

    expect($result['tenant'])->toBeNull();
});
