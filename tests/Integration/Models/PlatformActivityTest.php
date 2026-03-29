<?php

use App\Actions\Platform\LogPlatformActivity;
use App\Enums\SubscriptionTier;
use App\Models\PlatformActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

beforeEach(fn () => setUpCentralTest());

test('log creates record', function () {
    $activity = resolve(LogPlatformActivity::class)('tenant.created', 'tenant-1', 'A new tenant was created', ['plan' => SubscriptionTier::Pro->value]);

    $found = PlatformActivity::query()->where('event', 'tenant.created')->first();
    expect($found)->not->toBeNull()->and($found->tenant_id)->toBe('tenant-1')->and($found->description)->toBe('A new tenant was created')->and($found->metadata)->toBe(['plan' => SubscriptionTier::Pro->value]);
});

test('log works with null tenant id', function () {
    $activity = resolve(LogPlatformActivity::class)('system.update', null, 'System updated');

    expect($activity->tenant_id)->toBeNull();
    $found = PlatformActivity::query()->where('event', 'system.update')->first();
    expect($found)->not->toBeNull()->and($found->tenant_id)->toBeNull();
});

test('metadata is cast to array', function () {
    $activity = resolve(LogPlatformActivity::class)('test', 't1', 'desc', ['key' => 'value']);
    $activity->refresh();

    expect($activity->metadata)->toBeArray()->and($activity->metadata['key'])->toBe('value');
});

test('tenant relationship exists', function () {
    $activity = resolve(LogPlatformActivity::class)('test', null, 'desc');

    expect($activity->tenant())->toBeInstanceOf(BelongsTo::class);
});
