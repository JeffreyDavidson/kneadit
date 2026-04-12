<?php

use App\Filament\Central\Pages\FeatureUsage;
use App\Models\Platform\FeatureUsageLog;

beforeEach(function () {
    setUpCentralTest();
    test()->page = new FeatureUsage;
});

test('has data returns false when no logs exist', function () {
    expect(test()->page->getHasData())->toBeFalse();
});

test('has data returns true when logs exist', function () {
    FeatureUsageLog::factory()->create(['feature' => 'orders', 'usage_count' => 5]);

    expect(test()->page->getHasData())->toBeTrue();
});

test('get most used feature returns feature with highest total', function () {
    FeatureUsageLog::factory()->create(['feature' => 'orders', 'usage_count' => 10, 'date' => now()->toDateString()]);
    FeatureUsageLog::factory()->create(['feature' => 'products', 'usage_count' => 50, 'date' => now()->toDateString()]);
    FeatureUsageLog::factory()->create(['feature' => 'reviews', 'usage_count' => 3, 'date' => now()->toDateString()]);

    expect(test()->page->getMostUsedFeature())->toBe('products');
});

test('get most used feature returns null when no logs', function () {
    expect(test()->page->getMostUsedFeature())->toBeNull();
});

test('get least used feature returns feature with lowest total', function () {
    FeatureUsageLog::factory()->create(['feature' => 'orders', 'usage_count' => 10, 'date' => now()->toDateString()]);
    FeatureUsageLog::factory()->create(['feature' => 'reviews', 'usage_count' => 2, 'date' => now()->toDateString()]);

    expect(test()->page->getLeastUsedFeature())->toBe('reviews');
});

test('get least used feature returns null when no logs', function () {
    expect(test()->page->getLeastUsedFeature())->toBeNull();
});

test('get total interactions this month', function () {
    FeatureUsageLog::factory()->create(['feature' => 'orders', 'usage_count' => 10, 'date' => now()->toDateString()]);
    FeatureUsageLog::factory()->create(['feature' => 'products', 'usage_count' => 20, 'date' => now()->toDateString()]);
    // Previous month should not count
    FeatureUsageLog::factory()->create(['feature' => 'reviews', 'usage_count' => 99, 'date' => now()->subMonth()->toDateString()]);

    expect(test()->page->getTotalInteractionsThisMonth())->toBe(30);
});

test('get total interactions this month returns zero when empty', function () {
    expect(test()->page->getTotalInteractionsThisMonth())->toBe(0);
});

test('get feature usage bars returns sorted collection', function () {
    FeatureUsageLog::factory()->create(['feature' => 'orders', 'usage_count' => 10, 'date' => now()->toDateString()]);
    FeatureUsageLog::factory()->create(['feature' => 'products', 'usage_count' => 50, 'date' => now()->toDateString()]);

    $bars = test()->page->getFeatureUsageBars();

    expect($bars)->toHaveCount(2)
        ->and($bars->first()['feature'])->toBe('products')
        ->and($bars->first()['percent'])->toBe(100.0)
        ->and($bars->last()['feature'])->toBe('orders')
        ->and($bars->last()['percent'])->toBe(20.0);
});

test('get feature usage bars returns empty collection when no data', function () {
    expect(test()->page->getFeatureUsageBars())->toBeEmpty();
});

test('get heatmap data returns expected structure', function () {
    FeatureUsageLog::factory()->create(['feature' => 'orders', 'usage_count' => 5, 'date' => now()->toDateString()]);

    $data = test()->page->getHeatmapData();

    expect($data)->toHaveKeys(['days', 'rows'])
        ->and($data['days'])->toHaveCount(7)
        ->and($data['rows'])->toBeArray();
});

test('get heatmap data returns empty rows when no data', function () {
    $data = test()->page->getHeatmapData();

    expect($data['days'])->toHaveCount(7)
        ->and($data['rows'])->toBeEmpty();
});

test('select feature toggles selection', function () {
    test()->page->selectFeature('orders');
    expect(test()->page->selectedFeature)->toBe('orders');

    test()->page->selectFeature('orders');
    expect(test()->page->selectedFeature)->toBeNull();

    test()->page->selectFeature('products');
    expect(test()->page->selectedFeature)->toBe('products');
});

test('get feature tenant breakdown returns empty when no feature selected', function () {
    expect(test()->page->getFeatureTenantBreakdown())->toBeEmpty();
});

test('get feature tenant breakdown returns data for selected feature', function () {
    FeatureUsageLog::factory()->create(['feature' => 'orders', 'usage_count' => 10, 'tenant_id' => 'bakery-1', 'date' => now()->toDateString()]);
    FeatureUsageLog::factory()->create(['feature' => 'orders', 'usage_count' => 5, 'tenant_id' => 'bakery-2', 'date' => now()->toDateString()]);
    FeatureUsageLog::factory()->create(['feature' => 'products', 'usage_count' => 99, 'tenant_id' => 'bakery-1', 'date' => now()->toDateString()]);

    test()->page->selectedFeature = 'orders';
    $breakdown = test()->page->getFeatureTenantBreakdown();

    expect($breakdown)->toHaveCount(2)
        ->and($breakdown->first()->tenant_id)->toBe('bakery-1');
});

test('format feature name replaces underscores and capitalizes', function () {
    expect(test()->page->formatFeatureName('shopping_list'))->toBe('Shopping list')
        ->and(test()->page->formatFeatureName('orders'))->toBe('Orders');
});

test('selected feature defaults to null', function () {
    expect(test()->page->selectedFeature)->toBeNull();
});
