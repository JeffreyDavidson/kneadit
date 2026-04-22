<?php

use App\Mail\Customers\ReviewRequestMail;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('reviewUrl points at the storefront.submitReview route bound by order_number', function () {
    $order = Order::factory()->for(Customer::factory())->create();

    $mail = new ReviewRequestMail($order);

    expect($mail->reviewUrl)
        ->toContain($order->order_number)
        ->not->toContain("/review/{$order->id}");
});

test('reviewUrl matches what the actual route() helper produces', function () {
    $order = Order::factory()->for(Customer::factory())->create();

    $mail = new ReviewRequestMail($order);

    expect($mail->reviewUrl)->toBe(
        route('storefront.submitReview', ['order' => $order->order_number]),
    );
});
