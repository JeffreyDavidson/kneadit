<?php

use App\Filament\Central\Pages\FeatureUsage;
use App\Models\Platform\FeatureUsageLog;

beforeEach(function () {
    setUpCentralTest();
    test()->page = new FeatureUsage;
});

test('has data returns false when no logs exist', function () {
    expect(testFixture('page', FeatureUsage::class)->getHasData())->toBeFalse();
});

test('has data returns true when logs exist', function () {
    FeatureUsageLog::factory()->forFeature('orders')->create(['usage_count' => 5]);

    expect(testFixture('page', FeatureUsage::class)->getHasData())->toBeTrue();
});

test('get most used feature returns feature with highest total', function () {
    FeatureUsageLog::factory()->forFeature('orders')->create(['usage_count' => 10, 'date' => now()->toDateString()]);
    FeatureUsageLog::factory()->forFeature('products')->create(['usage_count' => 50, 'date' => now()->toDateString()]);
    FeatureUsageLog::factory()->forFeature('reviews')->create(['usage_count' => 3, 'date' => now()->toDateString()]);

    expect(testFixture('page', FeatureUsage::class)->getMostUsedFeature())->toBe('products');
});

test('get most used feature returns null when no logs', function () {
    expect(testFixture('page', FeatureUsage::class)->getMostUsedFeature())->toBeNull();
});

test('get least used feature returns feature with lowest total', function () {
    FeatureUsageLog::factory()->forFeature('orders')->create(['usage_count' => 10, 'date' => now()->toDateString()]);
    FeatureUsageLog::factory()->forFeature('reviews')->create(['usage_count' => 2, 'date' => now()->toDateString()]);

    expect(testFixture('page', FeatureUsage::class)->getLeastUsedFeature())->toBe('reviews');
});

test('get least used feature returns null when no logs', function () {
    expect(testFixture('page', FeatureUsage::class)->getLeastUsedFeature())->toBeNull();
});

test('get total interactions this month', function () {
    FeatureUsageLog::factory()->forFeature('orders')->create(['usage_count' => 10, 'date' => now()->toDateString()]);
    FeatureUsageLog::factory()->forFeature('products')->create(['usage_count' => 20, 'date' => now()->toDateString()]);
    // Previous month should not count
    FeatureUsageLog::factory()->forFeature('reviews')->create(['usage_count' => 99, 'date' => now()->subMonth()->toDateString()]);

    expect(testFixture('page', FeatureUsage::class)->getTotalInteractionsThisMonth())->toBe(30);
});

test('get total interactions this month returns zero when empty', function () {
    expect(testFixture('page', FeatureUsage::class)->getTotalInteractionsThisMonth())->toBe(0);
});

test('get feature usage bars returns sorted collection', function () {
    FeatureUsageLog::factory()->forFeature('orders')->create(['usage_count' => 10, 'date' => now()->toDateString()]);
    FeatureUsageLog::factory()->forFeature('products')->create(['usage_count' => 50, 'date' => now()->toDateString()]);

    $bars = testFixture('page', FeatureUsage::class)->getFeatureUsageBars();

    expect($bars)->toHaveCount(2)
        ->and($bars->first()['feature'])->toBe('products')
        ->and($bars->first()['percent'])->toBe(100.0)
        ->and($bars->last()['feature'])->toBe('orders')
        ->and($bars->last()['percent'])->toBe(20.0);
});

test('get feature usage bars returns empty collection when no data', function () {
    expect(testFixture('page', FeatureUsage::class)->getFeatureUsageBars())->toBeEmpty();
});

test('get heatmap data returns expected structure', function () {
    FeatureUsageLog::factory()->forFeature('orders')->create(['usage_count' => 5, 'date' => now()->toDateString()]);

    $data = testFixture('page', FeatureUsage::class)->getHeatmapData();

    expect($data)->toHaveKeys(['days', 'rows'])
        ->and($data['days'])->toHaveCount(7)
        ->and($data['rows'])->toBeArray();
});

test('get heatmap data returns empty rows when no data', function () {
    $data = testFixture('page', FeatureUsage::class)->getHeatmapData();

    expect($data['days'])->toHaveCount(7)
        ->and($data['rows'])->toBeEmpty();
});

test('select feature toggles selection', function () {
    testFixture('page', FeatureUsage::class)->selectFeature('orders');
    expect(testFixture('page', FeatureUsage::class)->selectedFeature)->toBe('orders');

    testFixture('page', FeatureUsage::class)->selectFeature('orders');
    expect(testFixture('page', FeatureUsage::class)->selectedFeature)->toBeNull();

    testFixture('page', FeatureUsage::class)->selectFeature('products');
    expect(testFixture('page', FeatureUsage::class)->selectedFeature)->toBe('products');
});

test('get feature tenant breakdown returns empty when no feature selected', function () {
    expect(testFixture('page', FeatureUsage::class)->getFeatureTenantBreakdown())->toBeEmpty();
});

test('get feature tenant breakdown returns data for selected feature', function () {
    FeatureUsageLog::factory()->forFeature('orders')->create(['usage_count' => 10, 'tenant_id' => 'bakery-1', 'date' => now()->toDateString()]);
    FeatureUsageLog::factory()->forFeature('orders')->create(['usage_count' => 5, 'tenant_id' => 'bakery-2', 'date' => now()->toDateString()]);
    FeatureUsageLog::factory()->forFeature('products')->create(['usage_count' => 99, 'tenant_id' => 'bakery-1', 'date' => now()->toDateString()]);

    testFixture('page', FeatureUsage::class)->selectedFeature = 'orders';
    $breakdown = testFixture('page', FeatureUsage::class)->getFeatureTenantBreakdown();

    expect($breakdown)->toHaveCount(2)
        ->and($breakdown->first()['tenant_id'])->toBe('bakery-1')
        ->and($breakdown->first())->toHaveKeys(['tenant_id', 'name', 'total']);
});

test('format feature name replaces underscores and capitalizes', function () {
    expect(testFixture('page', FeatureUsage::class)->formatFeatureName('shopping_list'))->toBe('Shopping list')
        ->and(testFixture('page', FeatureUsage::class)->formatFeatureName('orders'))->toBe('Orders');
});

test('selected feature defaults to null', function () {
    expect(testFixture('page', FeatureUsage::class)->selectedFeature)->toBeNull();
});
