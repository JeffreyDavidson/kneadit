<?php

use App\Filament\Pages\Operations\SeasonalItems;
use App\Models\Inventory\Product;
use App\Models\Inventory\SeasonalItem;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new SeasonalItems;
});

test('product id defaults to null', function () {
    expect(testFixture('page', SeasonalItems::class)->product_id)->toBeNull();
});

test('available from defaults to null', function () {
    expect(testFixture('page', SeasonalItems::class)->available_from)->toBeNull();
});

test('available until defaults to null', function () {
    expect(testFixture('page', SeasonalItems::class)->available_until)->toBeNull();
});

test('add seasonal item validates required fields', function () {
    expect(fn () => testFixture('page', SeasonalItems::class)->addSeasonalItem())
        ->toThrow(Illuminate\Validation\ValidationException::class);
});

test('add seasonal item validates date order', function () {
    $product = Product::factory()->create();

    testFixture('page', SeasonalItems::class)->product_id = $product->id;
    testFixture('page', SeasonalItems::class)->available_from = '2026-06-01';
    testFixture('page', SeasonalItems::class)->available_until = '2026-05-01'; // before from

    expect(fn () => testFixture('page', SeasonalItems::class)->addSeasonalItem())
        ->toThrow(Illuminate\Validation\ValidationException::class);
});

test('add seasonal item creates record', function () {
    $product = Product::factory()->create();

    testFixture('page', SeasonalItems::class)->product_id = $product->id;
    testFixture('page', SeasonalItems::class)->available_from = '2026-06-01';
    testFixture('page', SeasonalItems::class)->available_until = '2026-08-31';
    testFixture('page', SeasonalItems::class)->notes = 'Summer special';

    testFixture('page', SeasonalItems::class)->addSeasonalItem();

    expect(SeasonalItem::query()->count())->toBe(1)
        ->and(SeasonalItem::query()->first()->notes)->toBe('Summer special');
});

test('add seasonal item resets form fields', function () {
    $product = Product::factory()->create();

    testFixture('page', SeasonalItems::class)->product_id = $product->id;
    testFixture('page', SeasonalItems::class)->available_from = '2026-06-01';
    testFixture('page', SeasonalItems::class)->available_until = '2026-08-31';
    testFixture('page', SeasonalItems::class)->notes = 'Test';

    testFixture('page', SeasonalItems::class)->addSeasonalItem();

    $state = get_object_vars(testFixture('page', SeasonalItems::class));

    expect($state['product_id'])->toBeNull()
        ->and($state['available_from'])->toBeNull()
        ->and($state['available_until'])->toBeNull()
        ->and($state['notes'])->toBeNull();
});

test('delete seasonal item removes record', function () {
    $product = Product::factory()->create();
    $item = SeasonalItem::factory()->for($product)->create();

    testFixture('page', SeasonalItems::class)->deleteSeasonalItem($item->id);

    expect(SeasonalItem::query()->count())->toBe(0);
});

test('delete nonexistent seasonal item throws exception', function () {
    expect(fn () => testFixture('page', SeasonalItems::class)->deleteSeasonalItem(99999))
        ->toThrow(Illuminate\Database\Eloquent\ModelNotFoundException::class);
});

test('current items property returns collection', function () {
    expect(testFixture('page', SeasonalItems::class)->getCurrentItemsProperty())->toBeInstanceOf(Illuminate\Database\Eloquent\Collection::class);
});

test('upcoming items property returns collection', function () {
    expect(testFixture('page', SeasonalItems::class)->getUpcomingItemsProperty())->toBeInstanceOf(Illuminate\Database\Eloquent\Collection::class);
});

test('expired items property returns collection', function () {
    expect(testFixture('page', SeasonalItems::class)->getExpiredItemsProperty())->toBeInstanceOf(Illuminate\Database\Eloquent\Collection::class);
});
