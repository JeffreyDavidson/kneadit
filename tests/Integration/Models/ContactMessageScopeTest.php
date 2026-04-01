<?php

use App\Models\Customers\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('unread scope returns only unread messages', function () {
    $unread = ContactMessage::factory()->create(['is_read' => false]);
    ContactMessage::factory()->create(['is_read' => true]);

    $results = ContactMessage::query()->unread()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($unread->id);
});
