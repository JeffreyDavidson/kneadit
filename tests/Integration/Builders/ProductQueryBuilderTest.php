<?php

use App\Models\Inventory\Product;
use App\Models\Inventory\SeasonalItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('active scope returns only active products', function () {
    Product::factory()->active()->create();
    Product::factory()->inactive()->create();

    expect(Product::query()->active()->count())->toBe(1);
});

test('featured scope returns only featured products', function () {
    Product::factory()->active()->featured()->create();
    Product::factory()->active()->create();

    expect(Product::query()->featured()->count())->toBe(1);
});

test('availableOn returns products without seasonal items', function () {
    $product = Product::factory()->create();

    $results = Product::query()->availableOn(Date::parse('2026-10-15'))->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($product->id);
});

test('availableOn only returns seasonal products whose window covers the date', function (string $date, bool $available) {
    $product = Product::factory()->create();
    SeasonalItem::factory()->recycle($product)->create(['available_from' => '2026-10-01', 'available_until' => '2026-11-30']);

    $results = Product::query()->availableOn(Date::parse($date))->get();

    expect($results->pluck('id')->all())->toBe($available ? [$product->id] : []);
})->with([
    'before the window' => ['2026-09-30', false],
    'first day' => ['2026-10-01', true],
    'inside the window' => ['2026-10-15', true],
    'last day' => ['2026-11-30', true],
    'after the window' => ['2026-12-01', false],
]);

test('availableOn ignores the time of day on the date', function () {
    $product = Product::factory()->create();
    SeasonalItem::factory()->recycle($product)->create(['available_from' => '2026-10-01', 'available_until' => '2026-11-30']);

    $results = Product::query()->availableOn(Date::parse('2026-11-30 23:30'))->get();

    expect($results->pluck('id')->all())->toBe([$product->id]);
});

test('availableOn accepts a product with any one window covering the date', function () {
    $product = Product::factory()->create();
    SeasonalItem::factory()->recycle($product)->create(['available_from' => '2026-03-01', 'available_until' => '2026-04-30']);
    SeasonalItem::factory()->recycle($product)->create(['available_from' => '2026-10-01', 'available_until' => '2026-11-30']);

    $results = Product::query()->availableOn(Date::parse('2026-04-15'))->get();

    expect($results->pluck('id')->all())->toBe([$product->id]);
});
