<?php

use App\Filament\Central\Pages\FeatureUsage;
use App\Models\Platform\FeatureUsageLog;
use Illuminate\Support\Facades\Date;

beforeEach(function () {
    setUpCentralTest();
    test()->page = new FeatureUsage;
});

test('empty feature usage returns default analytics', function () {
    expect(test()->page->getHasData())->toBeFalse()
        ->and(test()->page->getMostUsedFeature())->toBeNull()
        ->and(test()->page->getLeastUsedFeature())->toBeNull()
        ->and(test()->page->getTotalInteractionsThisMonth())->toBe(0)
        ->and(test()->page->getFeatureUsageBars())->toBeEmpty();

    $heatmap = test()->page->getHeatmapData();

    expect($heatmap['days'])->toHaveCount(7)
        ->and($heatmap['rows'])->toBeEmpty()
        ->and(test()->page->getFeatureTenantBreakdown())->toBeEmpty()
        ->and(test()->page->selectedFeature)->toBeNull();
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
    FeatureUsageLog::factory()->forFeature('within-window')->create([
        'usage_count' => 7,
        'tenant_id' => 'bakery-1',
        'date' => Date::today()->subDays(6)->toDateString(),
    ]);
    FeatureUsageLog::factory()->forFeature('before-window')->create([
        'usage_count' => 50,
        'tenant_id' => 'bakery-1',
        'date' => Date::today()->subDays(7)->toDateString(),
    ]);
    $expectedMonthlyInteractions = 167
        + (Date::today()->subDays(6)->isSameMonth(Date::today()) ? 7 : 0)
        + (Date::today()->subDays(7)->isSameMonth(Date::today()) ? 50 : 0);

    expect(test()->page->getHasData())->toBeTrue()
        ->and(test()->page->getMostUsedFeature())->toBe('products')
        ->and(test()->page->getLeastUsedFeature())->toBe('reviews')
        ->and(test()->page->getTotalInteractionsThisMonth())->toBe($expectedMonthlyInteractions);

    $bars = test()->page->getFeatureUsageBars();

    expect($bars)->toHaveCount(6)
        ->and($bars->first()['feature'])->toBe('products')
        ->and($bars->first()['percent'])->toBe(100.0)
        ->and($bars->last()['feature'])->toBe('reviews')
        ->and($bars->last()['percent'])->toBe(1.0);

    $heatmap = test()->page->getHeatmapData();
    $rows = collect($heatmap['rows'])->keyBy('feature');
    $todayLabel = $heatmap['days'][6];
    $ordersToday = collect($rows->get('orders')['cells'])->firstWhere('date', $todayLabel);
    $productsToday = collect($rows->get('products')['cells'])->firstWhere('date', $todayLabel);
    $historicalToday = collect($rows->get('historical')['cells'])->firstWhere('date', $todayLabel);
    $withinWindowOldest = collect($rows->get('within-window')['cells'])->first();
    $beforeWindowOldest = collect($rows->get('before-window')['cells'])->first();
    expect($heatmap)->toHaveKeys(['days', 'rows'])
        ->and($heatmap['days'])->toHaveCount(7)
        ->and($rows->keys()->all())->toBe(['before-window', 'historical', 'orders', 'products', 'reviews', 'within-window'])
        ->and($ordersToday['count'])->toBe(15)
        ->and($ordersToday['intensity'])->toBe(0.1)
        ->and($productsToday['count'])->toBe(150)
        ->and($productsToday['intensity'])->toBe(1.0)
        ->and($historicalToday['count'])->toBe(0)
        ->and($historicalToday['intensity'])->toBe(0.0)
        ->and($withinWindowOldest['count'])->toBe(7)
        ->and($beforeWindowOldest['count'])->toBe(0);

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
    expect(test()->page->selectedFeature)->toBe('products')
        ->and(test()->page->formatFeatureName('shopping_list'))->toBe('Shopping list')
        ->and(test()->page->formatFeatureName('orders'))->toBe('Orders');
});
