<?php

use App\Filament\Pages\Platform\Messages;
use App\Models\Platform\PlatformMessage;

beforeEach(function () {
    setUpCentralTest();
    test()->page = new Messages;
});

test('viewing message defaults to null', function () {
    expect(testFixture('page', Messages::class)->viewingMessage)->toBeNull();
});

test('reply body defaults to empty', function () {
    expect(testFixture('page', Messages::class)->replyBody)->toBeEmpty();
});

test('get title returns messages', function () {
    expect(testFixture('page', Messages::class)->getTitle())->toBe('Messages');
});

test('view thread sets viewing message id', function () {
    $message = PlatformMessage::factory()->create([
        'tenant_id' => 'test',
    ]);

    testFixture('page', Messages::class)->viewThread($message->id);

    expect(testFixture('page', Messages::class)->viewingMessage)->toBe($message->id);
});

test('get thread returns null when no message viewed', function () {
    expect(testFixture('page', Messages::class)->getThread())->toBeNull();
});

test('get thread returns replies', function () {
    $parent = PlatformMessage::factory()->create(['tenant_id' => 'test']);
    PlatformMessage::factory()->create(['tenant_id' => 'test', 'parent_id' => $parent->id]);

    testFixture('page', Messages::class)->viewingMessage = $parent->id;
    $thread = testFixture('page', Messages::class)->getThread();

    expect($thread)->toHaveCount(1);
});

test('get viewing record returns null when no message viewed', function () {
    expect(testFixture('page', Messages::class)->getViewingRecord())->toBeNull();
});

test('get viewing record returns message', function () {
    $message = PlatformMessage::factory()->create(['tenant_id' => 'test']);

    testFixture('page', Messages::class)->viewingMessage = $message->id;

    $viewing = testFixture('page', Messages::class)->getViewingRecord();
    throw_unless($viewing instanceof PlatformMessage, RuntimeException::class, 'Expected a platform message.');
    expect($viewing->id)->toBe($message->id);
});

test('back to list resets state', function () {
    $message = PlatformMessage::factory()->create(['tenant_id' => 'test']);
    testFixture('page', Messages::class)->viewThread($message->id);
    testFixture('page', Messages::class)->replyBody = 'Draft reply';

    testFixture('page', Messages::class)->backToList();

    expect(testFixture('page', Messages::class)->viewingMessage)->toBeNull()
        ->and(testFixture('page', Messages::class)->replyBody)->toBeEmpty();
});

test('navigation badge color is warning', function () {
    expect(Messages::getNavigationBadgeColor())->toBe('warning');
});
