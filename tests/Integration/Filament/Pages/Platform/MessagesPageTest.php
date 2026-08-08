<?php

use App\Filament\Pages\Platform\Messages;
use App\Models\Platform\PlatformMessage;

beforeEach(function () {
    setUpCentralTest();
    test()->page = new Messages;
});

test('viewing message defaults to null', function () {
    expect(test()->page->viewingMessage)->toBeNull();
});

test('reply body defaults to empty', function () {
    expect(test()->page->replyBody)->toBeEmpty();
});

test('get title returns messages', function () {
    expect(test()->page->getTitle())->toBe('Messages');
});

test('view thread sets viewing message id', function () {
    $message = PlatformMessage::factory()->create([
        'tenant_id' => 'test',
    ]);

    test()->page->viewThread($message->id);

    expect(test()->page->viewingMessage)->toBe($message->id);
});

test('get thread returns null when no message viewed', function () {
    expect(test()->page->getThread())->toBeNull();
});

test('get thread returns replies', function () {
    $parent = PlatformMessage::factory()->create(['tenant_id' => 'test']);
    PlatformMessage::factory()->create(['tenant_id' => 'test', 'parent_id' => $parent->id]);

    test()->page->viewingMessage = $parent->id;
    $thread = test()->page->getThread();

    expect($thread)->toHaveCount(1);
});

test('get viewing record returns null when no message viewed', function () {
    expect(test()->page->getViewingRecord())->toBeNull();
});

test('get viewing record returns message', function () {
    $message = PlatformMessage::factory()->create(['tenant_id' => 'test']);

    test()->page->viewingMessage = $message->id;

    expect(test()->page->getViewingRecord())->not->toBeNull()
        ->and(test()->page->getViewingRecord()->id)->toBe($message->id);
});

test('back to list resets state', function () {
    $message = PlatformMessage::factory()->create(['tenant_id' => 'test']);
    test()->page->viewThread($message->id);
    test()->page->replyBody = 'Draft reply';

    test()->page->backToList();

    expect(test()->page->viewingMessage)->toBeNull()
        ->and(test()->page->replyBody)->toBeEmpty();
});

test('navigation badge color is warning', function () {
    expect(Messages::getNavigationBadgeColor())->toBe('warning');
});
