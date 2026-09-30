<?php

use App\Models\Operations\BusinessSchedule;
use App\Services\Scheduling\EarliestDeliveryDate;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings([
        'leadTimeHours' => 48,
        'timezone' => 'America/New_York',
    ])));
});

test('adds lead time to the bakery-local day and a day once its order cutoff passes', function (string $nowUtc, ?string $mondayCutoff, string $expected) {
    BusinessSchedule::factory()->open()->create(['day_of_week' => 1, 'order_cutoff_time' => $mondayCutoff]);
    Date::setTestNow($nowUtc);

    $earliest = resolve(EarliestDeliveryDate::class)->get();

    expect($earliest->toDateString())->toBe($expected);
})->with([
    'Monday 1 PM, before the 2 PM cutoff' => ['2026-10-05 17:00', '14:00', '2026-10-07'],
    'Monday 2 PM, at the cutoff' => ['2026-10-05 18:00', '14:00', '2026-10-08'],
    'Monday 3:30 PM, after the cutoff' => ['2026-10-05 19:30', '14:00', '2026-10-08'],
    'Monday 3:30 PM, no cutoff set' => ['2026-10-05 19:30', null, '2026-10-07'],
    'Monday 9 PM in New York is Tuesday in UTC' => ['2026-10-06 01:00', null, '2026-10-07'],
]);

test('only the ordering day cutoff applies', function () {
    BusinessSchedule::factory()->open()->create(['day_of_week' => 2, 'order_cutoff_time' => '09:00']);
    Date::setTestNow('2026-10-05 19:30');

    $earliest = resolve(EarliestDeliveryDate::class)->get();

    expect($earliest->toDateString())->toBe('2026-10-07');
});

test('uses UTC when the bakery has no timezone set', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['leadTimeHours' => 48])));
    Date::setTestNow('2026-10-06 01:00');

    $earliest = resolve(EarliestDeliveryDate::class)->get();

    expect($earliest->toDateString())->toBe('2026-10-08');
});
