<?php

use App\Actions\Inventory\AddSeasonalItem;
use App\Models\Inventory\Product;
use App\Models\Inventory\SeasonalItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('adds a seasonal item', function () {
    $product = Product::factory()->create();

    $seasonalItem = resolve(AddSeasonalItem::class)(
        productId: $product->id,
        availableFrom: '2026-06-01',
        availableUntil: '2026-08-31',
        notes: 'Summer special',
    );

    expect($seasonalItem)->toBeInstanceOf(SeasonalItem::class)
        ->and($seasonalItem->product_id)->toBe($product->id)
        ->and($seasonalItem->available_from->toDateString())->toBe('2026-06-01')
        ->and($seasonalItem->available_until->toDateString())->toBe('2026-08-31')
        ->and($seasonalItem->notes)->toBe('Summer special');
});
