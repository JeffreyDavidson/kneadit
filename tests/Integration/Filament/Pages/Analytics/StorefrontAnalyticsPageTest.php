<?php

use App\Filament\Pages\Analytics\StorefrontAnalytics;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new StorefrontAnalytics;
});

test('period defaults to week', function () {
    expect(testFixture('page', StorefrontAnalytics::class)->period)->toBe('week');
});

test('set period changes period', function () {
    testFixture('page', StorefrontAnalytics::class)->setPeriod('month');
    expect(testFixture('page', StorefrontAnalytics::class)->period)->toBe('month');

    testFixture('page', StorefrontAnalytics::class)->setPeriod('today');
    expect(testFixture('page', StorefrontAnalytics::class)->period)->toBe('today');

    testFixture('page', StorefrontAnalytics::class)->setPeriod('all');
    expect(testFixture('page', StorefrontAnalytics::class)->period)->toBe('all');
});

test('get total views returns integer', function () {
    expect(testFixture('page', StorefrontAnalytics::class)->getTotalViews())->toBeInt();
});

test('get unique visitors returns integer', function () {
    expect(testFixture('page', StorefrontAnalytics::class)->getUniqueVisitors())->toBeInt();
});

test('get most popular page returns string', function () {
    expect(testFixture('page', StorefrontAnalytics::class)->getMostPopularPage())->toBeString();
});

test('get conversion rate returns float', function () {
    expect(testFixture('page', StorefrontAnalytics::class)->getConversionRate())->toBeFloat();
});

test('get page views chart returns collection', function () {
    expect(testFixture('page', StorefrontAnalytics::class)->getPageViewsChart())->toBeInstanceOf(Illuminate\Support\Collection::class);
});

test('get daily trend returns collection', function () {
    expect(testFixture('page', StorefrontAnalytics::class)->getDailyTrend())->toBeInstanceOf(Illuminate\Support\Collection::class);
});

test('get top products returns collection', function () {
    expect(testFixture('page', StorefrontAnalytics::class)->getTopProducts())->toBeInstanceOf(Illuminate\Support\Collection::class);
});

test('get conversion funnel returns array', function () {
    expect(testFixture('page', StorefrontAnalytics::class)->getConversionFunnel())->toBeArray();
});
