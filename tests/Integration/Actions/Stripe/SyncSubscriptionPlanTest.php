<?php

use App\Actions\Stripe\SyncSubscriptionPlan;
use App\Enums\Platform\SubscriptionTier;
use App\Models\Platform\Tenant;

beforeEach(fn () => setUpCentralTest());

test('updates the tenant plan from the stripe price id', function () {
    createTenant(['plan' => 'starter']);
    $tenant = Tenant::query()->findOrFail('test-bakery');

    resolve(SyncSubscriptionPlan::class)(
        $tenant,
        'price_growth',
        ['price_growth' => 'growth', 'price_pro' => 'pro'],
    );

    expect($tenant->refresh()->plan)->toBe(SubscriptionTier::Growth);
});

test('does not update for an unknown price id', function () {
    createTenant(['plan' => 'starter']);
    $tenant = Tenant::query()->findOrFail('test-bakery');

    resolve(SyncSubscriptionPlan::class)(
        $tenant,
        'price_unknown',
        ['price_growth' => 'growth'],
    );

    expect($tenant->refresh()->plan)->toBe(SubscriptionTier::Starter);
});
