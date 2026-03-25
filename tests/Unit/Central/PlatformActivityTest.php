<?php

use App\Models\PlatformActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

beforeEach(fn () => setUpCentralTest());

test('log creates record', function () {
    $activity = PlatformActivity::log('tenant.created', 'tenant-1', 'A new tenant was created', ['plan' => 'pro']);

    $found = PlatformActivity::query()->where('event', 'tenant.created')->first();
    expect($found)->not->toBeNull();
    expect($found->tenant_id)->toBe('tenant-1');
    expect($found->description)->toBe('A new tenant was created');
    expect($found->metadata)->toBe(['plan' => 'pro']);
});

test('log works with null tenant id', function () {
    $activity = PlatformActivity::log('system.update', null, 'System updated');

    expect($activity->tenant_id)->toBeNull();
    $found = PlatformActivity::query()->where('event', 'system.update')->first();
    expect($found)->not->toBeNull();
    expect($found->tenant_id)->toBeNull();
});

test('metadata is cast to array', function () {
    $activity = PlatformActivity::log('test', 't1', 'desc', ['key' => 'value']);
    $activity->refresh();

    expect($activity->metadata)->toBeArray();
    expect($activity->metadata['key'])->toBe('value');
});

test('tenant relationship exists', function () {
    $activity = PlatformActivity::log('test', null, 'desc');

    expect($activity->tenant())->toBeInstanceOf(BelongsTo::class);
});
