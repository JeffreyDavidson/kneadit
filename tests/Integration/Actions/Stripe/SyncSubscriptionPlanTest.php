<?php

use App\Actions\Stripe\SyncSubscriptionPlan;
use Illuminate\Support\Facades\DB;

beforeEach(fn () => setUpCentralTest());

test('updates tenant plan from stripe price id', function () {
    createTenant(['email' => 'baker@test.com', 'plan' => 'starter']);

    resolve(SyncSubscriptionPlan::class)(
        'baker@test.com',
        'price_growth',
        ['price_growth' => 'growth', 'price_pro' => 'pro']
    );

    $tenant = DB::table('tenants')->where('id', 'test-bakery')->first();
    expect($tenant->plan)->toBe('growth');
});

test('does not update for unknown price id', function () {
    createTenant(['email' => 'baker@test.com', 'plan' => 'starter']);

    resolve(SyncSubscriptionPlan::class)(
        'baker@test.com',
        'price_unknown',
        ['price_growth' => 'growth']
    );

    $tenant = DB::table('tenants')->where('id', 'test-bakery')->first();
    expect($tenant->plan)->toBe('starter');
});
