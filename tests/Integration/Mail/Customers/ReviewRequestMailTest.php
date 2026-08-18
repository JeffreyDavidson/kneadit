<?php

use App\Mail\Customers\ReviewRequestMail;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('reviewUrl points at the storefront.submitReview route bound by order_number', function () {
    $order = Order::factory()->for(Customer::factory())->create();

    $mail = new ReviewRequestMail($order);

    expect($mail->reviewUrl)
        ->toContain($order->order_number)
        ->not->toContain("/review/{$order->id}");
});

test('reviewUrl is a temporary signed URL — the link is the proof of ownership', function () {
    $order = Order::factory()->for(Customer::factory())->create();

    $mail = new ReviewRequestMail($order);

    expect($mail->reviewUrl)
        ->toContain('signature=')
        ->toContain('expires=');
});
