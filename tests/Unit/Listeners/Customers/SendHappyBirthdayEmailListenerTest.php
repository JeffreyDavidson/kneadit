<?php

use App\Events\Customers\CustomerBirthday;
use App\Listeners\Customers\SendHappyBirthdayEmailListener;
use App\Mail\Customers\HappyBirthdayMail;
use App\Models\Customers\Customer;
use App\Models\Financial\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

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
