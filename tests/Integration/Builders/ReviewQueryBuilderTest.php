<?php

use App\Models\Engagement\Review;
use App\Models\Inventory\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->product = Product::factory()->create();
});

test('approved scope returns only approved reviews', function () {
    $approved = Review::factory()->recycle(testFixture('product', Product::class))->approved()->create();
    $pending = Review::factory()->recycle(testFixture('product', Product::class))->create();

    $results = Review::query()->approved()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($approved->id);
});

test('featured scope returns only featured reviews', function () {
    Review::factory()->recycle(testFixture('product', Product::class))->featured()->create();
    Review::factory()->recycle(testFixture('product', Product::class))->approved()->create();
    Review::factory()->recycle(testFixture('product', Product::class))->create();

    $results = Review::query()->featured()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->is_featured)->toBeTrue();
});

test('withProduct eager loads the product relationship', function () {
    Review::factory()->recycle(testFixture('product', Product::class))->create();

    $results = Review::query()->withProduct()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->relationLoaded('product'))->toBeTrue();
});

test('withComments returns only reviews that have non-empty comments', function () {
    Review::factory()->recycle(testFixture('product', Product::class))->create(['comment' => 'Great bread!']);
    Review::factory()->recycle(testFixture('product', Product::class))->create(['comment' => null]);
    Review::factory()->recycle(testFixture('product', Product::class))->create(['comment' => '']);

    $results = Review::query()->withComments()->get();

    expect($results)->toHaveCount(1);
});

test('forDisplay returns approved reviews with product', function () {
    $approved = Review::factory()->recycle(testFixture('product', Product::class))->approved()->create();
    Review::factory()->recycle(testFixture('product', Product::class))->create();

    $results = Review::query()->forDisplay()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->relationLoaded('product'))->toBeTrue();
});

test('statistics returns average rating and total count for approved reviews', function () {
    Review::factory()->recycle(testFixture('product', Product::class))->approved()->create(['rating' => 5]);
    Review::factory()->recycle(testFixture('product', Product::class))->approved()->create(['rating' => 3]);
    Review::factory()->recycle(testFixture('product', Product::class))->create(['rating' => 1]);

    $stats = Review::query()->statistics();

    expect((float) $stats->avg_rating)->toBe(4.0)
        ->and((int) $stats->total_count)->toBe(2);
});

test('statistics returns zero values when no approved reviews exist', function () {
    Review::factory()->recycle(testFixture('product', Product::class))->create(['rating' => 5]);

    $stats = Review::query()->statistics();

    expect((float) $stats->avg_rating)->toBe(0.0)
        ->and((int) $stats->total_count)->toBe(0);
});
