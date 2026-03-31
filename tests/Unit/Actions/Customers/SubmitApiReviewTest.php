<?php

use App\Actions\Customers\SubmitApiReview;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it creates a review with is_approved set to false', function () {
    $action = new SubmitApiReview;
    $review = $action([
        'customer_name' => 'Jane',
        'customer_email' => 'jane@example.com',
        'rating' => 5,
        'comment' => 'Great bakery!',
    ]);

    expect($review)->toBeInstanceOf(Review::class)
        ->and($review->customer_name)->toBe('Jane')
        ->and($review->rating)->toBe(5)
        ->and($review->is_approved)->toBeFalse();
});
