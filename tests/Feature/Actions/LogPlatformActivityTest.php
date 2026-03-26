<?php

use App\Actions\Platform\LogPlatformActivity;
use App\Enums\SubscriptionTier;
use App\Models\PlatformActivity;

beforeEach(fn () => setUpCentralTest());

test('it creates a platform activity record', function () {
    $activity = app(LogPlatformActivity::class)(
        'tenant.created',
        'tenant-1',
        'A new tenant was created',
        ['plan' => SubscriptionTier::Pro->value],
    );

    $found = PlatformActivity::query()->where('event', 'tenant.created')->first();
    expect($found)->not->toBeNull();
    expect($found->tenant_id)->toBe('tenant-1');
    expect($found->description)->toBe('A new tenant was created');
    expect($found->metadata)->toBe(['plan' => SubscriptionTier::Pro->value]);
});
