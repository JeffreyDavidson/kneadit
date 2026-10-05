<?php

use App\Models\Staff\User;
use App\Queries\Platform\PlatformRevenueMetricsQuery;
use Illuminate\Support\Facades\Date;
use Laravel\Cashier\Subscription;

beforeEach(function () {
    setUpCentralTest();
    Date::setTestNow('2026-10-05 12:00');
    config(['kneadit.stripe_prices' => [
        'starter' => 'price_starter_test',
        'growth' => 'price_growth_test',
        'pro' => 'price_pro_test',
    ]]);
});

/**
 * A bakery with its own owner, and optionally that owner's Cashier "default" subscription.
 *
 * @param  array<string, mixed>  $tenant
 * @param  array<string, mixed>  $subscription
 */
function bakeryWithSubscription(string $id, array $tenant = [], ?string $price = null, array $subscription = []): User
{
    $owner = User::factory()->owner()->create();
    createTenant(['id' => $id, 'name' => $id, 'email' => "{$id}@test.com", 'user_id' => $owner->id, ...$tenant]);

    if ($price !== null) {
        Subscription::factory()->for($owner, 'owner')->withPrice($price)->state($subscription)->create();
    }

    return $owner;
}

test('mrr counts valid paid subscriptions at their own price and nothing else', function () {
    bakeryWithSubscription('growth-bakery', price: 'price_growth_test');
    bakeryWithSubscription('starter-bakery', price: 'price_starter_test');

    // Not revenue: still on the bakery's free trial, comped, a demo, paused, or the subscription has ended.
    bakeryWithSubscription('trialing-bakery', ['trial_ends_at' => now()->addDays(10)]);
    bakeryWithSubscription('free-bakery', ['free_forever' => true], 'price_pro_test');
    bakeryWithSubscription('demo-bakery', ['is_demo' => true], 'price_pro_test');
    bakeryWithSubscription('paused-bakery', ['paused_at' => now()->subDays(40)], 'price_pro_test');
    bakeryWithSubscription('ended-bakery', price: 'price_pro_test', subscription: ['stripe_status' => 'canceled', 'ends_at' => now()->subDays(90)]);

    $metrics = resolve(PlatformRevenueMetricsQuery::class)->get();

    expect($metrics->mrr)->toBe(28)
        ->and($metrics->payingCount)->toBe(2)
        ->and($metrics->arpu)->toBe(14.0);
});

test('a subscription still in its Stripe trial is not revenue yet', function () {
    bakeryWithSubscription('stripe-trial-bakery', price: 'price_growth_test', subscription: ['stripe_status' => 'trialing', 'trial_ends_at' => now()->addDays(5)]);

    expect(resolve(PlatformRevenueMetricsQuery::class)->get()->mrr)->toBe(0);
});

test('mrr and arpu are zero with no paying bakeries', function () {
    $metrics = resolve(PlatformRevenueMetricsQuery::class)->get();

    expect($metrics->mrr)->toBe(0)->and($metrics->arpu)->toBe(0.0)->and($metrics->churnRate)->toBe(0.0);
});

test('churned counts subscriptions that ended and bakeries paused after their trial in the last 30 days', function () {
    bakeryWithSubscription('paying-bakery', price: 'price_growth_test');

    // Churned: the subscription ended five days ago.
    bakeryWithSubscription('ended-recently', price: 'price_starter_test', subscription: ['stripe_status' => 'canceled', 'ends_at' => now()->subDays(5)]);
    // Churned: paused three days ago, after a trial that ended 40 days ago.
    bakeryWithSubscription('paused-after-trial', ['trial_ends_at' => now()->subDays(40), 'paused_at' => now()->subDays(3)]);

    // Not churned: ended long ago, paused before the trial finished, deactivated without ever paying, comped.
    bakeryWithSubscription('ended-long-ago', price: 'price_starter_test', subscription: ['stripe_status' => 'canceled', 'ends_at' => now()->subDays(90)]);
    bakeryWithSubscription('paused-in-trial', ['trial_ends_at' => now()->addDays(5), 'paused_at' => now()->subDays(3)]);
    bakeryWithSubscription('deactivated', ['is_active' => false]);
    bakeryWithSubscription('free-bakery', ['free_forever' => true, 'trial_ends_at' => now()->subDays(40), 'paused_at' => now()->subDays(3)]);

    $metrics = resolve(PlatformRevenueMetricsQuery::class)->get();

    expect($metrics->churnedCount)->toBe(2)
        ->and($metrics->churnRate)->toBe(66.7);
});

test('converted counts bakeries whose owner pays after the trial; trialed counts finished trials', function () {
    $trialEnded = ['trial_ends_at' => now()->subDays(10)];

    bakeryWithSubscription('converted', $trialEnded, 'price_growth_test');
    bakeryWithSubscription('not-converted', $trialEnded);
    bakeryWithSubscription('stripe-trial', $trialEnded, 'price_growth_test', ['stripe_status' => 'trialing', 'trial_ends_at' => now()->addDays(5)]);

    // Not counted: still in the trial, comped, or a demo.
    bakeryWithSubscription('in-trial', ['trial_ends_at' => now()->addDays(5)]);
    bakeryWithSubscription('free-bakery', [...$trialEnded, 'free_forever' => true], 'price_pro_test');
    bakeryWithSubscription('demo-bakery', [...$trialEnded, 'is_demo' => true], 'price_pro_test');

    $metrics = resolve(PlatformRevenueMetricsQuery::class)->get();

    expect($metrics->trialedCount)->toBe(3)
        ->and($metrics->convertedCount)->toBe(1)
        ->and($metrics->trialConversion)->toBe(33.3);
});
