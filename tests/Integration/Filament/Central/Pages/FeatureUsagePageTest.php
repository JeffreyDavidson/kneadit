<?php

use App\Filament\Central\Pages\FeatureUsage;
use App\Models\Platform\FeatureUsageLog;

beforeEach(function () {
    setUpCentralTest();
    test()->page = new FeatureUsage;
});

test('empty feature usage returns default analytics', function () {
    expect(test()->page->getHasData())->toBeFalse();
    expect(test()->page->getMostUsedFeature())->toBeNull();
    expect(test()->page->getLeastUsedFeature())->toBeNull();
    expect(test()->page->getTotalInteractionsThisMonth())->toBe(0);
    expect(test()->page->getFeatureUsageBars())->toBeEmpty();

    $heatmap = test()->page->getHeatmapData();

    expect($heatmap['days'])->toHaveCount(7)
        ->and($heatmap['rows'])->toBeEmpty();

    expect(test()->page->getFeatureTenantBreakdown())->toBeEmpty();
    expect(test()->page->selectedFeature)->toBeNull();
});

test('populated feature usage returns aggregated analytics', function () {
    FeatureUsageLog::factory()->forFeature('orders')->create([
        'usage_count' => 10,
        'tenant_id' => 'bakery-1',
        'date' => now()->toDateString(),
    ]);
    FeatureUsageLog::factory()->forFeature('orders')->create([
        'usage_count' => 5,
        'tenant_id' => 'bakery-2',
        'date' => now()->toDateString(),
    ]);
    FeatureUsageLog::factory()->forFeature('products')->create([
        'usage_count' => 150,
        'tenant_id' => 'bakery-1',
        'date' => now()->toDateString(),
    ]);
    FeatureUsageLog::factory()->forFeature('reviews')->create([
        'usage_count' => 2,
        'tenant_id' => 'bakery-1',
        'date' => now()->toDateString(),
    ]);
    FeatureUsageLog::factory()->forFeature('historical')->create([
        'usage_count' => 99,
        'tenant_id' => 'bakery-1',
        'date' => now()->subMonth()->toDateString(),
    ]);

    expect(test()->page->getHasData())->toBeTrue();
    expect(test()->page->getMostUsedFeature())->toBe('products');
    expect(test()->page->getLeastUsedFeature())->toBe('reviews');
    expect(test()->page->getTotalInteractionsThisMonth())->toBe(167);

    $bars = test()->page->getFeatureUsageBars();

    expect($bars)->toHaveCount(4)
        ->and($bars->first()['feature'])->toBe('products')
        ->and($bars->first()['percent'])->toBe(100.0)
        ->and($bars->last()['feature'])->toBe('reviews')
        ->and($bars->last()['percent'])->toBe(1.0);

    $heatmap = test()->page->getHeatmapData();

    expect($heatmap)->toHaveKeys(['days', 'rows'])
        ->and($heatmap['days'])->toHaveCount(7)
        ->and($heatmap['rows'])->toBeArray();

    test()->page->selectedFeature = 'orders';
    $breakdown = test()->page->getFeatureTenantBreakdown();

    expect($breakdown)->toHaveCount(2)
        ->and($breakdown->first()['tenant_id'])->toBe('bakery-1')
        ->and($breakdown->first())->toHaveKeys(['tenant_id', 'name', 'total']);
});

test('feature usage selection and labels update locally', function () {
    test()->page->selectFeature('orders');
    expect(test()->page->selectedFeature)->toBe('orders');

    test()->page->selectFeature('orders');
    expect(test()->page->selectedFeature)->toBeNull();

    test()->page->selectFeature('products');
    expect(test()->page->selectedFeature)->toBe('products');

    expect(test()->page->formatFeatureName('shopping_list'))->toBe('Shopping list')
        ->and(test()->page->formatFeatureName('orders'))->toBe('Orders');
});
