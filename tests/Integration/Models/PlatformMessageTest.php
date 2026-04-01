<?php

use App\Models\Platform\PlatformMessage;

beforeEach(fn () => setUpCentralTest());

test('can create message', function () {
    $msg = PlatformMessage::factory()->create([
        'tenant_id' => 't1',
        'subject' => 'Welcome',
        'body' => 'Hello there',
    ]);

    expect(PlatformMessage::query()->where('subject', 'Welcome')->first())->not->toBeNull();
});

test('scope unread', function () {
    PlatformMessage::factory()->create(['tenant_id' => 't1', 'is_read' => false]);
    PlatformMessage::factory()->create(['tenant_id' => 't1', 'is_read' => true]);

    expect(PlatformMessage::unread()->get())->toHaveCount(1);
});

test('scope from admin', function () {
    PlatformMessage::factory()->fromAdmin()->create(['tenant_id' => 't1']);
    PlatformMessage::factory()->fromTenant()->create(['tenant_id' => 't1']);

    expect(PlatformMessage::fromAdmin()->get())->toHaveCount(1);
});

test('scope from tenant', function () {
    PlatformMessage::factory()->fromTenant()->create(['tenant_id' => 't1']);
    PlatformMessage::factory()->fromAdmin()->create(['tenant_id' => 't1']);

    expect(PlatformMessage::fromTenant()->get())->toHaveCount(1);
});

test('scope top level', function () {
    $parent = PlatformMessage::factory()->create(['tenant_id' => 't1']);
    PlatformMessage::factory()->create(['tenant_id' => 't1', 'parent_id' => $parent->id]);

    expect(PlatformMessage::topLevel()->get())->toHaveCount(1);
});

test('replies relationship', function () {
    $parent = PlatformMessage::factory()->create(['tenant_id' => 't1']);
    PlatformMessage::factory()->fromTenant()->create(['tenant_id' => 't1', 'parent_id' => $parent->id]);

    expect($parent->replies)->toHaveCount(1);
});

test('parent relationship', function () {
    $parent = PlatformMessage::factory()->create(['tenant_id' => 't1']);
    $reply = PlatformMessage::factory()->fromTenant()->create(['tenant_id' => 't1', 'parent_id' => $parent->id]);

    expect($reply->parent->id)->toBe($parent->id);
});
