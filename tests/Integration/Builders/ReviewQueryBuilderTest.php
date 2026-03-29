<?php

use App\Models\Product;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->product = Product::factory()->create();
});

test('approved scope returns only approved reviews', function () {
    $approved = Review::factory()->recycle($this->product)->approved()->create();
    $pending = Review::factory()->recycle($this->product)->create();

    $results = Review::query()->approved()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($approved->id);
});

test('forDisplay returns approved reviews with product', function () {
    $approved = Review::factory()->recycle($this->product)->approved()->create();
    Review::factory()->recycle($this->product)->create();

    $results = Review::query()->forDisplay()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->relationLoaded('product'))->toBeTrue();
});
