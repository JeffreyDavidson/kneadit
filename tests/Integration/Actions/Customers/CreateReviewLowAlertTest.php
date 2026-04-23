<?php

use App\Actions\Customers\CreateReview;
use App\Events\Customers\LowReviewReceived;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    settings(['low_review_alert_threshold' => 2]);
});

test('fires LowReviewReceived for a 1-star review', function () {
    Event::fake();
    $order = Order::factory()->for(Customer::factory()->create(['name' => 'Alice']))->create();

    resolve(CreateReview::class)($order, rating: 1, comment: 'Cold and dry');

    Event::assertDispatched(LowReviewReceived::class);
});

test('fires LowReviewReceived at the exact threshold', function () {
    Event::fake();
    $order = Order::factory()->for(Customer::factory()->create())->create();

    resolve(CreateReview::class)($order, rating: 2);

    Event::assertDispatched(LowReviewReceived::class);
});

test('does not fire above threshold', function () {
    Event::fake();
    $order = Order::factory()->for(Customer::factory()->create())->create();

    resolve(CreateReview::class)($order, rating: 4, comment: 'Pretty good');

    Event::assertNotDispatched(LowReviewReceived::class);
});

test('does not fire when threshold is 0 (disabled)', function () {
    settings(['low_review_alert_threshold' => 0]);
    Event::fake();
    $order = Order::factory()->for(Customer::factory()->create())->create();

    resolve(CreateReview::class)($order, rating: 1);

    Event::assertNotDispatched(LowReviewReceived::class);
});
