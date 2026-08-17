<?php

use App\Models\Platform\PlatformAnnouncement;

beforeEach(fn () => setUpCentralTest());

test('can create announcement', function () {
    $ann = PlatformAnnouncement::factory()->create([
        'title' => 'Maintenance',
        'body' => 'Scheduled downtime',
        'type' => 'warning',
        'target_plans' => ['pro', 'enterprise'],
    ]);

    expect(PlatformAnnouncement::query()->where('title', 'Maintenance')->first())->not->toBeNull();
});

test('target plans is cast to array', function () {
    $ann = PlatformAnnouncement::factory()->create([
        'target_plans' => ['free', 'pro'],
    ]);

    $ann->refresh();
    expect($ann->target_plans)->toBeArray()->toBe(['free', 'pro']);
});

test('is active defaults to true', function () {
    $ann = PlatformAnnouncement::factory()->create();

    expect($ann->refresh()->is_active)->toBeTrue();
});
