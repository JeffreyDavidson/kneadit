<?php

use App\Events\Customers\CustomerBirthday;
use App\Listeners\Customers\SendHappyBirthdayEmailListener;
use App\Mail\Customers\HappyBirthdayMail;
use App\Models\Customers\Customer;
use App\Models\Financial\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it sends happy birthday email to the customer', function () {
    Mail::fake();

    $customer = Customer::factory()->create(['email' => 'birthday@example.com']);
    $coupon = Coupon::factory()->create();
    $event = new CustomerBirthday($customer, $coupon);

    $listener = new SendHappyBirthdayEmailListener;
    $listener->handle($event);

    Mail::assertQueued(HappyBirthdayMail::class, fn (HappyBirthdayMail $mail) => $mail->hasTo('birthday@example.com'));
});

test('failed method logs a warning with customer name and error message', function () {
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn (string $message, array $context): bool => $message === 'SendHappyBirthdayEmailListener failed'
            && $context['customer'] === 'Jane Doe'
            && $context['error'] === 'SMTP timeout');

    $customer = Customer::factory()->create(['name' => 'Jane Doe']);
    $coupon = Coupon::factory()->create();
    $event = new CustomerBirthday($customer, $coupon);

    $listener = new SendHappyBirthdayEmailListener;
    $listener->failed($event, new RuntimeException('SMTP timeout'));
});
