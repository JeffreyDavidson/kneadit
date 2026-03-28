<?php

use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('approved scope returns only approved reviews', function () {
    $approved = Review::factory()->approved()->create();
    $pending = Review::factory()->create();

    $results = Review::query()->approved()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($approved->id);
});

test('forDisplay returns approved reviews with product', function () {
    $approved = Review::factory()->approved()->create();
    Review::factory()->create();

    $results = Review::query()->forDisplay()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->relationLoaded('product'))->toBeTrue();
});
