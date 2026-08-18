<?php

use App\Actions\Customers\SubmitContactMessage;
use App\Events\Customers\ContactMessageReceived;
use App\Listeners\Customers\NotifyBakerOfContactMessageListener;
use App\Mail\Customers\NewContactMessageNotificationMail;
use App\Models\Customers\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();
});

test('SubmitContactMessage fires ContactMessageReceived', function () {
    Event::fake();

    $message = resolve(SubmitContactMessage::class)([
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'subject' => 'Hi',
        'message' => 'Just saying hello',
    ]);

    Event::assertDispatched(fn (ContactMessageReceived $e): bool => $e->message->is($message));
});

test('listener queues notification to the configured store email', function () {
    settings(['store_email' => 'baker@example.com']);
    $message = ContactMessage::query()->create([
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'subject' => 'Hi',
        'message' => 'Just saying hello',
    ]);

    (new NotifyBakerOfContactMessageListener)->handle(new ContactMessageReceived($message));

    Mail::assertQueued(
        NewContactMessageNotificationMail::class,
        fn (NewContactMessageNotificationMail $mail): bool => $mail->hasTo('baker@example.com')
            && $mail->message->is($message),
    );
});

test('listener skips when no store email is configured', function () {
    settings(['store_email' => '']);
    $message = ContactMessage::query()->create([
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'subject' => 'Hello',
        'message' => 'Hello',
    ]);

    (new NotifyBakerOfContactMessageListener)->handle(new ContactMessageReceived($message));

    Mail::assertNothingQueued();
});
