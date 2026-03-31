<?php

use App\Events\BirthdayDiscountGenerated;
use App\Listeners\SendBirthdayDiscountEmailListener;
use App\Mail\BirthdayDiscountMail;
use App\Models\Coupon;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
