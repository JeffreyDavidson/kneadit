<?php

use App\Models\Staff\User;
use App\Queries\Platform\TenantAnalyticsSummaryQuery;
use App\Queries\Platform\TenantSubscriptionAnalyticsQuery;
use Illuminate\Support\Facades\Date;
use Laravel\Cashier\Subscription;

beforeEach(function () {
    setUpCentralTest();
    Date::setTestNow('2026-10-05 12:00');
    config(['kneadit.stripe_prices' => ['growth' => 'price_growth_test']]);

    analyticsBakery('converted', ['trial_ends_at' => now()->subDays(10)], paying: true);
    analyticsBakery('lapsed', ['trial_ends_at' => now()->subDays(10)]);
    analyticsBakery('trialing', ['trial_ends_at' => now()->addDays(5)]);

    // Never in the funnel: no trial, comped or a demo. They must not count as converted.
    analyticsBakery('no-trial');
    analyticsBakery('comped', ['free_forever' => true, 'trial_ends_at' => now()->subDays(10)], paying: true);
    analyticsBakery('demo', ['is_demo' => true, 'trial_ends_at' => now()->subDays(10)]);
});

/**
 * @param  array<string, mixed>  $tenant
 */
function analyticsBakery(string $id, array $tenant = [], bool $paying = false): void
{
    $owner = User::factory()->owner()->create();
    createTenant(['id' => $id, 'name' => $id, 'email' => "{$id}@test.com", 'user_id' => $owner->id, ...$tenant]);

    if ($paying) {
        Subscription::factory()->for($owner, 'owner')->withPrice('price_growth_test')->create();
    }
}

test('trial conversion counts finished trials by whether the owner really pays', function () {
    expect(resolve(TenantSubscriptionAnalyticsQuery::class)->trialConversion())->toBe([
        'on_trial' => 1,
        'expired' => 1,
        'converted' => 1,
        'paying' => 1,
    ]);
});

test('the analytics KPIs take active subscriptions and conversion from real subscription state', function () {
    $kpis = collect(resolve(TenantAnalyticsSummaryQuery::class)->kpis())->keyBy('label');

    expect($kpis['Active Subscriptions']['value'])->toBe('1')
        ->and($kpis['Active Subscriptions']['hint'])->toBe('1 on trial • 1 churned')
        ->and($kpis['Trial → Paid']['value'])->toBe('50%')
        ->and($kpis['Trial → Paid']['hint'])->toBe('1 of 2 completed trials converted');
});

test('the tenant status chart counts paying bakeries as active', function () {
    expect(resolve(TenantAnalyticsSummaryQuery::class)->tenantStatus())->toBe([
        'Active' => 1,
        'On trial' => 1,
        'Trial expired' => 1,
    ]);
});
