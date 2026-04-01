<?php

use App\Models\Engagement\PageView;
use App\Queries\Analytics\StorefrontAnalyticsQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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
