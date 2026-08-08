<?php

use App\Events\Customers\ReviewRequested;
use App\Listeners\Customers\SendReviewRequestEmailListener;
use App\Mail\Customers\ReviewRequestMail;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

pest()->use(RefreshDatabase::class);

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

test('failed method logs a warning with order number and error message', function () {
    Log::shouldReceive('warning')
        ->once()
        ->with('SendReviewRequestEmailListener failed', Mockery::on(fn (array $context) => $context['order'] === 'ORD-001'
            && $context['error'] === 'SMTP timeout'));

    $order = Order::factory()->create(['order_number' => 'ORD-001']);
    $event = new ReviewRequested($order);

    $listener = new SendReviewRequestEmailListener;
    $listener->failed($event, new RuntimeException('SMTP timeout'));
});
