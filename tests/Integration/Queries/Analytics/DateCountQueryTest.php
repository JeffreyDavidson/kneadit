<?php

use App\Models\Engagement\PageView;
use App\Models\Inventory\Product;
use App\Queries\Analytics\DateCountQuery;
use Illuminate\Support\Facades\Date;

beforeEach(fn () => setUpTenantTest());

test('counts created rows per local calendar day', function (string $timezone, array $createdAt, array $expected) {
    foreach ($createdAt as $timestamp) {
        PageView::factory()->create(['product_id' => null, 'created_at' => $timestamp]);
    }

    $counts = DateCountQuery::countByLocalDay(
        PageView::query(),
        Date::parse('2026-10-04'),
        Date::parse('2026-10-06'),
        new DateTimeZone($timezone),
    );

    expect($counts)->toBe($expected);
})->with([
    'behind UTC' => ['America/New_York', ['2026-10-06 00:30', '2026-10-05 03:30', '2026-10-05 04:00', '2026-10-07 03:59'], ['2026-10-04' => 1, '2026-10-05' => 2, '2026-10-06' => 1]],
    'ahead of UTC' => ['Australia/Sydney', ['2026-10-05 15:00', '2026-10-05 12:59', '2026-10-03 13:59', '2026-10-06 13:00'], ['2026-10-05' => 1, '2026-10-06' => 1]],
    'UTC' => ['UTC', ['2026-10-04 00:00', '2026-10-04 23:59', '2026-10-07 00:00'], ['2026-10-04' => 2]],
]);

test('limits the count to the given query', function () {
    PageView::factory()->create(['product_id' => null, 'created_at' => '2026-10-05 12:00']);
    PageView::factory()->create(['product_id' => Product::factory(), 'created_at' => '2026-10-05 12:00']);

    $counts = DateCountQuery::countByLocalDay(
        PageView::query()->whereNull('product_id'),
        Date::parse('2026-10-05'),
        Date::parse('2026-10-05'),
        new DateTimeZone('America/New_York'),
    );

    expect($counts)->toBe(['2026-10-05' => 1]);
});
