<?php

use App\Models\PlatformMessage;

beforeEach(fn () => setUpCentralTest());

test('can create message', function () {
    $msg = PlatformMessage::create([
        'tenant_id' => 't1',
        'sender_type' => 'admin',
        'subject' => 'Welcome',
        'body' => 'Hello there',
    ]);

    expect(PlatformMessage::where('subject', 'Welcome')->first())->not->toBeNull();
});

test('scope unread', function () {
    PlatformMessage::create(['tenant_id' => 't1', 'sender_type' => 'admin', 'subject' => 'S1', 'body' => 'B', 'is_read' => false]);
    PlatformMessage::create(['tenant_id' => 't1', 'sender_type' => 'admin', 'subject' => 'S2', 'body' => 'B', 'is_read' => true]);

    expect(PlatformMessage::unread()->get())->toHaveCount(1);
});

test('scope from admin', function () {
    PlatformMessage::create(['tenant_id' => 't1', 'sender_type' => 'admin', 'subject' => 'S', 'body' => 'B']);
    PlatformMessage::create(['tenant_id' => 't1', 'sender_type' => 'tenant', 'subject' => 'S', 'body' => 'B']);

    expect(PlatformMessage::fromAdmin()->get())->toHaveCount(1);
});

test('scope from tenant', function () {
    PlatformMessage::create(['tenant_id' => 't1', 'sender_type' => 'tenant', 'subject' => 'S', 'body' => 'B']);
    PlatformMessage::create(['tenant_id' => 't1', 'sender_type' => 'admin', 'subject' => 'S', 'body' => 'B']);

    expect(PlatformMessage::fromTenant()->get())->toHaveCount(1);
});

test('scope top level', function () {
    $parent = PlatformMessage::create(['tenant_id' => 't1', 'sender_type' => 'admin', 'subject' => 'S', 'body' => 'B']);
    PlatformMessage::create(['tenant_id' => 't1', 'sender_type' => 'admin', 'subject' => 'S', 'body' => 'Reply', 'parent_id' => $parent->id]);

    expect(PlatformMessage::topLevel()->get())->toHaveCount(1);
});

test('replies relationship', function () {
    $parent = PlatformMessage::create(['tenant_id' => 't1', 'sender_type' => 'admin', 'subject' => 'S', 'body' => 'B']);
    PlatformMessage::create(['tenant_id' => 't1', 'sender_type' => 'tenant', 'subject' => 'S', 'body' => 'Reply', 'parent_id' => $parent->id]);

    expect($parent->replies)->toHaveCount(1);
});

test('parent relationship', function () {
    $parent = PlatformMessage::create(['tenant_id' => 't1', 'sender_type' => 'admin', 'subject' => 'S', 'body' => 'B']);
    $reply = PlatformMessage::create(['tenant_id' => 't1', 'sender_type' => 'tenant', 'subject' => 'S', 'body' => 'Reply', 'parent_id' => $parent->id]);

    expect($reply->parent->id)->toBe($parent->id);
});
