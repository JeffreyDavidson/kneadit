<?php

use App\Events\Marketing\CateringInquiryReceived;
use App\Listeners\Marketing\NotifyBakerOfCateringInquiryListener;
use App\Mail\Marketing\NewCateringInquiryNotificationMail;
use App\Models\Customers\CateringInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();
});

test('queues the inquiry notification to the configured store email', function () {
    settings(['store_email' => 'baker@example.com']);
    $inquiry = CateringInquiry::factory()->create();

    (new NotifyBakerOfCateringInquiryListener)->handle(new CateringInquiryReceived($inquiry));

    Mail::assertQueued(
        NewCateringInquiryNotificationMail::class,
        fn (NewCateringInquiryNotificationMail $mail): bool => $mail->hasTo('baker@example.com')
            && $mail->inquiry->is($inquiry),
    );
});

test('skips when no store email is configured', function () {
    settings(['store_email' => '']);
    $inquiry = CateringInquiry::factory()->create();

    (new NotifyBakerOfCateringInquiryListener)->handle(new CateringInquiryReceived($inquiry));

    Mail::assertNothingQueued();
});
