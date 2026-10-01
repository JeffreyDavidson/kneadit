<?php

use App\Services\Scheduling\BakeryClock;
use App\Services\Settings\TenantSettings;
use Illuminate\Support\Facades\Date;

function bindBakeryTimezone(string $timezone): void
{
    app()->instance(
        TenantSettings::class,
        makeTenantSettings(orders: makeOrderSettings(['timezone' => $timezone])),
    );
}

test('today follows the tenant settings bound at call time', function () {
    Date::setTestNow('2026-10-05 20:00');
    bindBakeryTimezone('America/New_York');
    $clock = resolve(BakeryClock::class);
    $newYork = $clock->today()->toDateString();

    bindBakeryTimezone('Australia/Sydney');
    $sydney = $clock->today()->toDateString();

    expect($newYork)->toBe('2026-10-05')
        ->and($sydney)->toBe('2026-10-06');
});

test('now follows the tenant settings bound at call time', function () {
    Date::setTestNow('2026-10-05 20:00');
    bindBakeryTimezone('America/New_York');
    $clock = resolve(BakeryClock::class);
    $newYork = $clock->now()->format('Y-m-d H:i');

    bindBakeryTimezone('Australia/Sydney');
    $sydney = $clock->now()->format('Y-m-d H:i');

    expect($newYork)->toBe('2026-10-05 16:00')
        ->and($sydney)->toBe('2026-10-06 07:00');
});
