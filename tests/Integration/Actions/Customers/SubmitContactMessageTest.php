<?php

use App\Actions\Customers\SubmitContactMessage;
use App\Models\Customers\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('creates contact message from data', function () {
    $message = resolve(SubmitContactMessage::class)([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'subject' => 'Custom order',
        'message' => 'I need a birthday cake.',
    ]);

    expect($message)->toBeInstanceOf(ContactMessage::class)
        ->and($message->name)->toBe('Jane Doe')
        ->and($message->email)->toBe('jane@example.com')
        ->and($message->subject)->toBe('Custom order');
});
