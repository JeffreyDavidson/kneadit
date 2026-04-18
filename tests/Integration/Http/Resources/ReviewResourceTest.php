<?php

use App\Http\Resources\ReviewResource;
use App\Models\Engagement\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('review resource projects the JSON:API shape', function () {
    $review = Review::factory()->create();
    $resource = new ReviewResource($review);
    $request = request();

    expect($resource->toType($request))->toBe('reviews')
        ->and($resource->toId($request))->toBe((string) $review->id);

    $attributes = $resource->toAttributes($request);

    expect($attributes)->toHaveKeys(['customer_name', 'rating', 'comment', 'created_at']);
});
