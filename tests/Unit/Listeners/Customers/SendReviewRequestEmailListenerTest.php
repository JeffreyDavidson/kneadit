<?php

use App\Events\Customers\ReviewRequested;
use App\Listeners\Customers\SendReviewRequestEmailListener;
use App\Mail\Customers\ReviewRequestMail;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it sends review request email to the customer', function () {
    Mail::fake();

    $customer = Customer::factory()->create(['email' => 'reviewer@example.com']);
    $order = Order::factory()->for($customer)->create();
    $event = new ReviewRequested($order);

    $listener = new SendReviewRequestEmailListener;
    $listener->handle($event);

    Mail::assertQueued(ReviewRequestMail::class, fn (ReviewRequestMail $mail) => $mail->hasTo('reviewer@example.com'));
});
