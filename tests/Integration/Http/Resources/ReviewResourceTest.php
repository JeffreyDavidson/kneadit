<?php

use App\Http\Resources\ReviewResource;
use App\Models\Engagement\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('review resource returns correct structure', function () {
    $review = Review::factory()->create();
    $resource = new ReviewResource($review);
    $data = $resource->toArray(request());

    expect($data)->toHaveKeys(['customer_name', 'rating', 'comment', 'created_at']);
});
