<?php

use App\Actions\Customers\CreateReview;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('creates review from order with rating and comment', function () {
    $order = Order::factory()->create();

    $review = resolve(CreateReview::class)($order, 5, 'Amazing bread!');

    expect($review)->toBeInstanceOf(Review::class)
        ->and($review->rating)->toBe(5)
        ->and($review->comment)->toBe('Amazing bread!')
        ->and($review->order_id)->toBe($order->id)
        ->and($review->is_approved)->toBeFalse();
});
