<?php

use App\Actions\Content\ApproveReview;
use App\Models\Engagement\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it approves a review', function () {
    $review = Review::factory()->create(['is_approved' => false]);

    resolve(ApproveReview::class)($review);

    expect($review->fresh()->is_approved)->toBeTrue();
});
