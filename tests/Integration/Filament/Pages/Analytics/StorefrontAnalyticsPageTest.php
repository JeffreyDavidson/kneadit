<?php

use App\Filament\Pages\Analytics\StorefrontAnalytics;

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
    expect(test()->page->getPageViewsChart())->toBeInstanceOf(Illuminate\Support\Collection::class);
});

test('get daily trend returns collection', function () {
    expect(test()->page->getDailyTrend())->toBeInstanceOf(Illuminate\Support\Collection::class);
});

test('get top products returns collection', function () {
    expect(test()->page->getTopProducts())->toBeInstanceOf(Illuminate\Support\Collection::class);
});

test('get conversion funnel returns array', function () {
    expect(test()->page->getConversionFunnel())->toBeArray();
});
