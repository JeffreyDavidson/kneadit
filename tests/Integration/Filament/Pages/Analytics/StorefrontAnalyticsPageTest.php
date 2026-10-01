<?php

use App\Filament\Pages\Analytics\StorefrontAnalytics;
use App\Models\Engagement\PageView;
use App\Services\Settings\TenantSettings;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new StorefrontAnalytics;
});

test('period defaults to week', function () {
    expect(test()->page->period)->toBe('week');
});

test('set period changes period', function () {
    test()->page->setPeriod('month');
    expect(test()->page->period)->toBe('month');

    test()->page->setPeriod('today');
    expect(test()->page->period)->toBe('today');

    test()->page->setPeriod('all');
    expect(test()->page->period)->toBe('all');
});

test('get total views returns integer', function () {
    expect(test()->page->getTotalViews())->toBeInt();
});

test('get unique visitors returns integer', function () {
    expect(test()->page->getUniqueVisitors())->toBeInt();
});

test('get most popular page returns string', function () {
    expect(test()->page->getMostPopularPage())->toBeString();
});

test('get conversion rate returns float', function () {
    expect(test()->page->getConversionRate())->toBeFloat();
});

test('get page views chart returns collection', function () {
    expect(test()->page->getPageViewsChart())->toBeInstanceOf(Collection::class);
});

test('get daily trend returns collection', function () {
    expect(test()->page->getDailyTrend())->toBeInstanceOf(Collection::class);
});

test('get top products returns collection', function () {
    expect(test()->page->getTopProducts())->toBeInstanceOf(Collection::class);
});

test('get conversion funnel returns array', function () {
    expect(test()->page->getConversionFunnel())->toBeArray();
});

test('counts the bakery-local day as today', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow('2026-10-06 01:00');
    // 16:00 on 2026-10-05 in New York, 20:30 the same evening, and 23:00 the night before.
    PageView::factory()->create(['product_id' => null, 'created_at' => '2026-10-05 20:00']);
    PageView::factory()->create(['product_id' => null, 'created_at' => '2026-10-06 00:30']);
    PageView::factory()->create(['product_id' => null, 'created_at' => '2026-10-05 03:00']);
    test()->page->setPeriod('today');

    $views = test()->page->getTotalViews();

    expect($views)->toBe(2);
});

test('starts the week on the bakery-local Monday', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    // Sunday 22:00 in New York, still the week that began Monday 2026-09-28.
    Date::setTestNow('2026-10-05 02:00');
    PageView::factory()->create(['product_id' => null, 'created_at' => '2026-09-28 05:00']);
    PageView::factory()->create(['product_id' => null, 'created_at' => '2026-09-28 03:00']);
    test()->page->setPeriod('week');

    $views = test()->page->getTotalViews();

    expect($views)->toBe(1);
});

test('buckets the daily trend by the bakery-local day', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow('2026-10-06 01:00');
    // 16:00 and 20:30 on 2026-10-05 in New York, then 23:00 on 2026-10-04.
    PageView::factory()->create(['product_id' => null, 'created_at' => '2026-10-05 20:00']);
    PageView::factory()->create(['product_id' => null, 'created_at' => '2026-10-06 00:30']);
    PageView::factory()->create(['product_id' => null, 'created_at' => '2026-10-05 03:00']);

    $trend = test()->page->getDailyTrend();

    expect($trend->pluck('views', 'date')->all())->toBe(['2026-10-04' => 1, '2026-10-05' => 2]);
});
