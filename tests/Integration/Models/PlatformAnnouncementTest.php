<?php

use App\Models\PlatformAnnouncement;

beforeEach(fn () => setUpCentralTest());

test('can create announcement', function () {
    $ann = PlatformAnnouncement::query()->create([
        'title' => 'Maintenance',
        'body' => 'Scheduled downtime',
        'type' => 'warning',
        'target_plans' => ['pro', 'enterprise'],
        'is_active' => true,
    ]);

    expect(PlatformAnnouncement::query()->where('title', 'Maintenance')->first())->not->toBeNull();
});

test('target plans is cast to array', function () {
    $ann = PlatformAnnouncement::query()->create([
        'title' => 'T',
        'body' => 'B',
        'target_plans' => ['free', 'pro'],
    ]);

    $ann->refresh();
    expect($ann->target_plans)->toBeArray();
    expect($ann->target_plans)->toBe(['free', 'pro']);
});

test('is active defaults to true', function () {
    $ann = PlatformAnnouncement::query()->create(['title' => 'T', 'body' => 'B']);

    expect($ann->fresh()->is_active)->toBeTrue();
});
