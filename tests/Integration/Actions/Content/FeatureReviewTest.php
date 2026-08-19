<?php

use App\Actions\Content\FeatureReview;
use App\Models\Engagement\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it features a review', function () {
    $review = Review::factory()->create();

    resolve(FeatureReview::class)($review);

    expect($review->refresh()->is_featured)->toBeTrue();
});
