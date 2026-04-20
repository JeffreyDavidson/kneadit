<?php

use App\Models\Customers\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('unread scope returns only unread messages', function () {
    $unread = ContactMessage::factory()->unread()->create();
    ContactMessage::factory()->read()->create();

    $results = ContactMessage::query()->unread()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($unread->id);
});

test('read scope returns only read messages', function () {
    ContactMessage::factory()->unread()->create();
    $read = ContactMessage::factory()->read()->create();

    $results = ContactMessage::query()->read()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($read->id);
});
