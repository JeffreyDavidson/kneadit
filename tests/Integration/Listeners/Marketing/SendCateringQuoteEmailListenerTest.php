<?php

use App\Events\Marketing\CateringQuoteRequested;
use App\Listeners\Marketing\SendCateringQuoteEmailListener;
use App\Mail\Marketing\CateringQuoteMail;
use App\Models\Customers\CateringInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it sends catering quote email to the customer', function () {
    Mail::fake();

    $inquiry = CateringInquiry::factory()->create(['customer_email' => 'customer@example.com']);
    $event = new CateringQuoteRequested($inquiry);

    $listener = new SendCateringQuoteEmailListener;
    $listener->handle($event);

    Mail::assertQueued(CateringQuoteMail::class, fn (CateringQuoteMail $mail) => $mail->hasTo('customer@example.com'));
});

test('failed method logs a warning with inquiry id and error message', function () {
    Log::shouldReceive('warning')
        ->once()
        ->with('SendCateringQuoteEmailListener failed', Mockery::on(fn (array $context) => $context['inquiry'] === 1
            && $context['error'] === 'SMTP timeout'));

    $inquiry = CateringInquiry::factory()->create(['id' => 1]);
    $event = new CateringQuoteRequested($inquiry);

    $listener = new SendCateringQuoteEmailListener;
    $listener->failed($event, new RuntimeException('SMTP timeout'));
});
