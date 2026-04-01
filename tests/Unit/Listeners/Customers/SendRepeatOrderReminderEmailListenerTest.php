<?php

use App\Events\Customers\RepeatOrderReminderDue;
use App\Listeners\Customers\SendRepeatOrderReminderEmailListener;
use App\Mail\Customers\RepeatOrderReminderMail;
use App\Models\Customers\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it sends repeat order reminder email to the customer', function () {
    Mail::fake();

    $customer = Customer::factory()->create(['email' => 'loyal@example.com']);
    $event = new RepeatOrderReminderDue($customer, 30);

    $listener = new SendRepeatOrderReminderEmailListener;
    $listener->handle($event);

    Mail::assertQueued(RepeatOrderReminderMail::class, fn (RepeatOrderReminderMail $mail) => $mail->hasTo('loyal@example.com'));
});
