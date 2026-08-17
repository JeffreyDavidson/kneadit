<?php

use App\Filament\Pages\Operations\SeasonalItems;
use App\Models\Inventory\Product;
use App\Models\Inventory\SeasonalItem;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new SeasonalItems;
});

test('product id defaults to null', function () {
    expect(test()->page->product_id)->toBeNull();
});

test('available from defaults to null', function () {
    expect(test()->page->available_from)->toBeNull();
});

test('available until defaults to null', function () {
    expect(test()->page->available_until)->toBeNull();
});

test('add seasonal item validates required fields', function () {
    expect(fn () => test()->page->addSeasonalItem())
        ->toThrow(Illuminate\Validation\ValidationException::class);
});

test('add seasonal item validates date order', function () {
    $product = Product::factory()->create();

    test()->page->product_id = $product->id;
    test()->page->available_from = '2026-06-01';
    test()->page->available_until = '2026-05-01'; // before from

    expect(fn () => test()->page->addSeasonalItem())
        ->toThrow(Illuminate\Validation\ValidationException::class);
});

test('add seasonal item creates record', function () {
    $product = Product::factory()->create();

    test()->page->product_id = $product->id;
    test()->page->available_from = '2026-06-01';
    test()->page->available_until = '2026-08-31';
    test()->page->notes = 'Summer special';

    test()->page->addSeasonalItem();

    expect(SeasonalItem::query()->count())->toBe(1)
        ->and(SeasonalItem::query()->firstOrFail()->notes)->toBe('Summer special');
});

test('add seasonal item resets form fields', function () {
    $product = Product::factory()->create();

    test()->page->product_id = $product->id;
    test()->page->available_from = '2026-06-01';
    test()->page->available_until = '2026-08-31';
    test()->page->notes = 'Test';

    test()->page->addSeasonalItem();

    $state = get_object_vars(test()->page);

    expect($state['product_id'])->toBeNull()
        ->and($state['available_from'])->toBeNull()
        ->and($state['available_until'])->toBeNull()
        ->and($state['notes'])->toBeNull();
});

test('delete seasonal item removes record', function () {
    $product = Product::factory()->create();
    $item = SeasonalItem::factory()->for($product)->create();

    test()->page->deleteSeasonalItem($item->id);

    expect(SeasonalItem::query()->count())->toBe(0);
});

test('delete nonexistent seasonal item throws exception', function () {
    expect(fn () => test()->page->deleteSeasonalItem(99999))
        ->toThrow(Illuminate\Database\Eloquent\ModelNotFoundException::class);
});

test('current items property returns collection', function () {
    expect(test()->page->getCurrentItemsProperty())->toBeInstanceOf(Illuminate\Database\Eloquent\Collection::class);
});

test('upcoming items property returns collection', function () {
    expect(test()->page->getUpcomingItemsProperty())->toBeInstanceOf(Illuminate\Database\Eloquent\Collection::class);
});

test('expired items property returns collection', function () {
    expect(test()->page->getExpiredItemsProperty())->toBeInstanceOf(Illuminate\Database\Eloquent\Collection::class);
});
