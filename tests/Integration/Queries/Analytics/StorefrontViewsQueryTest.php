<?php

use App\Models\Engagement\PageView;
use App\Queries\Analytics\StorefrontViewsQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Date::setTestNow('2026-08-18 12:00:00');
});

afterEach(fn () => Date::setTestNow());

test('returns a normalized seven day storefront view series with trend', function () {
    PageView::factory()->count(2)->create(['product_id' => null, 'created_at' => Date::today()->subDay()]);
    PageView::factory()->count(3)->create(['product_id' => null, 'created_at' => Date::today()]);
    PageView::factory()->count(4)->create(['created_at' => Date::today()]);

    $data = resolve(StorefrontViewsQuery::class)->get();

    expect($data['today'])->toBe(3)
        ->and($data['trend'])->toBe(50)
        ->and($data['chart'])->toBe([0, 0, 0, 0, 0, 67, 100]);
});
