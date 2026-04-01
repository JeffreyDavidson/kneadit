<?php

use App\Actions\Content\FeatureReview;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it features a review', function () {
    $review = Review::factory()->create(['is_featured' => false]);

    resolve(FeatureReview::class)($review);

    expect($review->fresh()->is_featured)->toBeTrue();
});
