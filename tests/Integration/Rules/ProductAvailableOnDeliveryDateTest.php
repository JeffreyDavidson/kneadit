<?php

use App\Models\Inventory\Product;
use App\Models\Inventory\SeasonalItem;
use App\Rules\ProductAvailableOnDeliveryDate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

/**
 * @param  list<array<string, mixed>>  $items
 */
function productAvailabilityErrors(array $items, ?string $deliveryDate): MessageBag
{
    return validator(
        ['delivery_date' => $deliveryDate, 'items' => $items],
        ['items.*.product_id' => [new ProductAvailableOnDeliveryDate]],
    )->errors();
}

test('a seasonal product is only orderable inside one of its windows', function (array $windows, string $deliveryDate, bool $available) {
    $product = Product::factory()->create(['name' => 'Pumpkin Loaf']);
    foreach ($windows as [$from, $until]) {
        SeasonalItem::factory()->recycle($product)->create(['available_from' => $from, 'available_until' => $until]);
    }

    $errors = productAvailabilityErrors([['product_id' => $product->id, 'quantity' => 1]], $deliveryDate);

    expect($errors->has('items.0.product_id'))->toBe(! $available);
})->with([
    'no season' => [[], '2026-10-15', true],
    'inside the window' => [[['2026-10-01', '2026-11-30']], '2026-10-15', true],
    'on available_from' => [[['2026-10-01', '2026-11-30']], '2026-10-01', true],
    'on available_until' => [[['2026-10-01', '2026-11-30']], '2026-11-30', true],
    'the day before' => [[['2026-10-01', '2026-11-30']], '2026-09-30', false],
    'the day after' => [[['2026-10-01', '2026-11-30']], '2026-12-01', false],
    'in the first of two windows' => [[['2026-03-01', '2026-04-30'], ['2026-10-01', '2026-11-30']], '2026-04-15', true],
    'in the second of two windows' => [[['2026-03-01', '2026-04-30'], ['2026-10-01', '2026-11-30']], '2026-10-15', true],
    'between two windows' => [[['2026-03-01', '2026-04-30'], ['2026-10-01', '2026-11-30']], '2026-07-15', false],
]);

test('the error names the product and the delivery date', function () {
    $product = Product::factory()->create(['name' => 'Pumpkin Loaf']);
    SeasonalItem::factory()->recycle($product)->create(['available_from' => '2026-10-01', 'available_until' => '2026-11-30']);

    $errors = productAvailabilityErrors([['product_id' => $product->id, 'quantity' => 1]], '2026-12-25');

    expect($errors->first('items.0.product_id'))->toBe("Pumpkin Loaf isn't available for Dec 25, 2026.");
});

test('only the out-of-season item is flagged', function () {
    $inSeason = Product::factory()->create();
    $outOfSeason = Product::factory()->create();
    SeasonalItem::factory()->recycle($outOfSeason)->create(['available_from' => '2026-10-01', 'available_until' => '2026-11-30']);

    $errors = productAvailabilityErrors([
        ['product_id' => $inSeason->id, 'quantity' => 1],
        ['product_id' => $outOfSeason->id, 'quantity' => 1],
    ], '2026-12-25');

    expect($errors->keys())->toBe(['items.1.product_id']);
});

test('the products are loaded once however many items are ordered', function () {
    $products = Product::factory()->count(3)->create();
    SeasonalItem::factory()->recycle($products->first())->create(['available_from' => '2026-10-01', 'available_until' => '2026-11-30']);
    $countQueries = function (array $items): int {
        DB::flushQueryLog();
        DB::enableQueryLog();
        productAvailabilityErrors($items, '2026-12-25');

        return count(DB::getQueryLog());
    };

    $single = $countQueries([['product_id' => $products[0]->id, 'quantity' => 1]]);
    $triple = $countQueries($products->map(fn (Product $product): array => ['product_id' => $product->id, 'quantity' => 1])->all());

    expect($triple)->toBe($single);
});

test('the rule leaves missing products to the exists rule', function (mixed $productId) {
    $errors = productAvailabilityErrors([['product_id' => $productId, 'quantity' => 1]], '2026-12-25');

    expect($errors->has('items.0.product_id'))->toBeFalse();
})->with([
    'a product that does not exist' => 999999,
    'a non-numeric product' => 'abc',
]);

test('the rule leaves an unusable delivery date to the date rules', function (?string $deliveryDate) {
    $product = Product::factory()->create();
    SeasonalItem::factory()->recycle($product)->create(['available_from' => '2026-10-01', 'available_until' => '2026-11-30']);

    $errors = productAvailabilityErrors([['product_id' => $product->id, 'quantity' => 1]], $deliveryDate);

    expect($errors->has('items.0.product_id'))->toBeFalse();
})->with([
    'a missing delivery date' => null,
    'an unparseable delivery date' => 'not-a-date',
]);
