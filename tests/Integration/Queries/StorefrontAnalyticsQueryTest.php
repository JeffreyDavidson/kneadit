<?php

use App\Models\Engagement\PageView;
use App\Queries\Analytics\StorefrontAnalyticsQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('returns total views for a date range', function () {
    PageView::query()->insert([
        ['page' => 'home', 'session_id' => 'a', 'created_at' => now()],
        ['page' => 'menu', 'session_id' => 'b', 'created_at' => now()],
        ['page' => 'home', 'session_id' => 'a', 'created_at' => now()->subMonth()],
    ]);

    $query = new StorefrontAnalyticsQuery(now()->startOfWeek());

    expect($query->totalViews())->toBe(2);
});

test('returns unique visitors', function () {
    PageView::query()->insert([
        ['page' => 'home', 'session_id' => 'a', 'created_at' => now()],
        ['page' => 'menu', 'session_id' => 'a', 'created_at' => now()],
        ['page' => 'home', 'session_id' => 'b', 'created_at' => now()],
    ]);

    $query = new StorefrontAnalyticsQuery(now()->startOfWeek());

    expect($query->uniqueVisitors())->toBe(2);
});

test('conversion rate returns zero when no order page views', function () {
    $query = new StorefrontAnalyticsQuery(now()->startOfWeek());

    expect($query->conversionRate())->toBe(0.0);
});

test('conversion rate calculates correctly with orders', function () {
    PageView::query()->insert([
        ['page' => 'order', 'session_id' => 'a', 'created_at' => now()],
        ['page' => 'order', 'session_id' => 'b', 'created_at' => now()],
    ]);

    // Create an order in the same date range
    App\Models\Orders\Order::factory()->create(['created_at' => now()]);

    $query = new StorefrontAnalyticsQuery(now()->startOfWeek());

    // 1 order / 2 order views = 50%
    expect($query->conversionRate())->toBe(50.0);
});

test('conversion funnel returns all steps with percentages and dropoff', function () {
    PageView::query()->insert([
        ['page' => 'home', 'session_id' => 'a', 'created_at' => now()],
        ['page' => 'home', 'session_id' => 'b', 'created_at' => now()],
        ['page' => 'menu', 'session_id' => 'a', 'created_at' => now()],
        ['page' => 'order', 'session_id' => 'a', 'created_at' => now()],
    ]);

    $query = new StorefrontAnalyticsQuery(now()->startOfWeek());
    $funnel = $query->conversionFunnel();

    expect($funnel)->toHaveCount(4)
        ->and($funnel[0]['label'])->toBe('Home')
        ->and($funnel[0]['count'])->toBe(2)
        ->and($funnel[0]['percentage'])->toBe(100.0)
        ->and($funnel[0]['dropoff'])->toBeNull()
        ->and($funnel[1]['label'])->toBe('Menu')
        ->and($funnel[1]['dropoff'])->toBe(50.0);
});
