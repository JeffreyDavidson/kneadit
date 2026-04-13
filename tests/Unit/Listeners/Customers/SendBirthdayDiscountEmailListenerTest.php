<?php

use App\Events\Customers\BirthdayDiscountGenerated;
use App\Listeners\Customers\SendBirthdayDiscountEmailListener;
use App\Mail\Customers\BirthdayDiscountMail;
use App\Models\Customers\Customer;
use App\Models\Financial\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it sends birthday discount email to the customer', function () {
    Mail::fake();

    $customer = Customer::factory()->create(['email' => 'birthday@example.com']);
    $coupon = Coupon::factory()->create();
    $event = new BirthdayDiscountGenerated($customer, $coupon);

    $listener = new SendBirthdayDiscountEmailListener;
    $listener->handle($event);

    Mail::assertQueued(BirthdayDiscountMail::class, fn (BirthdayDiscountMail $mail) => $mail->hasTo('birthday@example.com'));
});

test('failed method logs a warning with customer name and error message', function () {
    Log::shouldReceive('warning')
        ->once()
        ->with('SendBirthdayDiscountEmailListener failed', Mockery::on(fn (array $context) => $context['customer'] === 'Jane Doe'
            && $context['error'] === 'SMTP timeout'));

    $customer = Customer::factory()->create(['name' => 'Jane Doe']);
    $coupon = Coupon::factory()->create();
    $event = new BirthdayDiscountGenerated($customer, $coupon);

    $listener = new SendBirthdayDiscountEmailListener;
    $listener->failed($event, new RuntimeException('SMTP timeout'));
});
