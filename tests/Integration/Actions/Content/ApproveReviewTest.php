<?php

use App\Actions\Content\ApproveReview;
use App\Models\Engagement\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it approves a review', function () {
    $review = Review::factory()->pending()->create();

    resolve(ApproveReview::class)($review);

    expect($review->fresh()->is_approved)->toBeTrue();
});
