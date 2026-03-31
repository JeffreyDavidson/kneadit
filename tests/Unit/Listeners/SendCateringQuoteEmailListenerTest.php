<?php

use App\Events\CateringQuoteRequested;
use App\Listeners\SendCateringQuoteEmailListener;
use App\Mail\CateringQuoteMail;
use App\Models\CateringInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it sends catering quote email to the customer', function () {
    Mail::fake();

    $inquiry = CateringInquiry::factory()->create(['customer_email' => 'customer@example.com']);
    $event = new CateringQuoteRequested($inquiry);

    $listener = new SendCateringQuoteEmailListener;
    $listener->handle($event);

    Mail::assertQueued(CateringQuoteMail::class, fn (CateringQuoteMail $mail) => $mail->hasTo('customer@example.com'));
});
