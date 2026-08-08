<?php

use App\Events\Customers\LowReviewReceived;
use App\Listeners\Customers\SendLowReviewAlertListener;
use App\Mail\Operations\LowReviewAlertMail;
use App\Models\Customers\Customer;
use App\Models\Engagement\Review;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();
});

test('queues an alert to the configured store email', function () {
    settings(['store_email' => 'baker@example.com']);

    $review = Review::factory()->create([
        'customer_name' => 'Alice',
        'customer_email' => 'alice@example.com',
        'order_id' => Order::factory()->for(Customer::factory()->create())->create()->id,
        'rating' => 1,
        'comment' => 'Burnt',
    ]);

    (new SendLowReviewAlertListener)->handle(new LowReviewReceived($review));

    Mail::assertQueued(
        LowReviewAlertMail::class,
        fn (LowReviewAlertMail $mail): bool => $mail->hasTo('baker@example.com')
            && $mail->review->is($review),
    );
});

test('skips when no store email is configured', function () {
    settings(['store_email' => '']);

    $review = Review::factory()->create([
        'customer_name' => 'Alice',
        'customer_email' => 'alice@example.com',
        'order_id' => Order::factory()->for(Customer::factory()->create())->create()->id,
        'rating' => 1,
    ]);

    (new SendLowReviewAlertListener)->handle(new LowReviewReceived($review));

    Mail::assertNothingQueued();
});
