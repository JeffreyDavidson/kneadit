<?php

use App\Enums\Orders\DeliveryType;
use App\Models\Operations\BlockedDate;
use App\Models\Operations\BusinessSchedule;
use App\Models\Orders\Order;
use App\Services\Scheduling\PickupSlotResolver;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('returns empty when feature is disabled', function () {
    settings(['pickup_slots_enabled' => false]);
    $date = Date::parse('2026-05-04'); // Monday

    BusinessSchedule::factory()->create([
        'day_of_week' => $date->dayOfWeek,
        'is_open' => true,
        'open_time' => '08:00',
        'close_time' => '12:00',
    ]);

    expect(resolve(PickupSlotResolver::class)->availableSlots($date->toDateString()))->toBeEmpty();
});

test('returns empty when there is no schedule for the day', function () {
    settings(['pickup_slots_enabled' => true]);

    expect(resolve(PickupSlotResolver::class)->availableSlots('2026-05-04'))->toBeEmpty();
});

test('returns empty when the bakery is closed that day', function () {
    settings(['pickup_slots_enabled' => true]);
    $date = Date::parse('2026-05-04');

    BusinessSchedule::factory()->create([
        'day_of_week' => $date->dayOfWeek,
        'is_open' => false,
        'open_time' => '08:00',
        'close_time' => '12:00',
    ]);

    expect(resolve(PickupSlotResolver::class)->availableSlots($date->toDateString()))->toBeEmpty();
});

test('generates slots stepped by interval between open and close', function () {
    settings([
        'pickup_slots_enabled' => true,
        'pickup_slot_interval_minutes' => 30,
        'pickup_slot_max_per_window' => 3,
    ]);
    $date = Date::parse('2026-05-04');

    BusinessSchedule::factory()->create([
        'day_of_week' => $date->dayOfWeek,
        'is_open' => true,
        'open_time' => '08:00',
        'close_time' => '10:00',
    ]);

    $slots = resolve(PickupSlotResolver::class)->availableSlots($date->toDateString());

    expect($slots)->toBe(['08:00', '08:30', '09:00', '09:30']);
});

test('honors a 15-minute interval', function () {
    settings([
        'pickup_slots_enabled' => true,
        'pickup_slot_interval_minutes' => 15,
        'pickup_slot_max_per_window' => 3,
    ]);
    $date = Date::parse('2026-05-04');

    BusinessSchedule::factory()->create([
        'day_of_week' => $date->dayOfWeek,
        'is_open' => true,
        'open_time' => '09:00',
        'close_time' => '10:00',
    ]);

    $slots = resolve(PickupSlotResolver::class)->availableSlots($date->toDateString());

    expect($slots)->toBe(['09:00', '09:15', '09:30', '09:45']);
});

test('filters out slots that are already at the per-window cap', function () {
    settings([
        'pickup_slots_enabled' => true,
        'pickup_slot_interval_minutes' => 30,
        'pickup_slot_max_per_window' => 2,
    ]);
    $date = Date::parse('2026-05-04');

    BusinessSchedule::factory()->create([
        'day_of_week' => $date->dayOfWeek,
        'is_open' => true,
        'open_time' => '08:00',
        'close_time' => '10:00',
    ]);

    Order::factory()->confirmed()->count(2)->create([
        'delivery_date' => $date->toDateString(),
        'delivery_time' => '08:30',
        'delivery_type' => DeliveryType::Pickup->value,
    ]);

    $slots = resolve(PickupSlotResolver::class)->availableSlots($date->toDateString());

    expect($slots)->toBe(['08:00', '09:00', '09:30']);
});

test('ignores delivery-typed orders when counting slot bookings', function () {
    settings([
        'pickup_slots_enabled' => true,
        'pickup_slot_interval_minutes' => 30,
        'pickup_slot_max_per_window' => 1,
    ]);
    $date = Date::parse('2026-05-04');

    BusinessSchedule::factory()->create([
        'day_of_week' => $date->dayOfWeek,
        'is_open' => true,
        'open_time' => '08:00',
        'close_time' => '09:00',
    ]);

    Order::factory()->confirmed()->create([
        'delivery_date' => $date->toDateString(),
        'delivery_time' => '08:00',
        'delivery_type' => DeliveryType::Delivery->value,
    ]);

    $slots = resolve(PickupSlotResolver::class)->availableSlots($date->toDateString());

    expect($slots)->toContain('08:00');
});

test('a partial-day blocked date replaces the schedule hours with its special hours', function () {
    settings([
        'pickup_slots_enabled' => true,
        'pickup_slot_interval_minutes' => 30,
        'pickup_slot_max_per_window' => 3,
    ]);
    $date = Date::parse('2026-05-04');
    BusinessSchedule::factory()->create([
        'day_of_week' => $date->dayOfWeek,
        'is_open' => true,
        'open_time' => '08:00',
        'close_time' => '12:00',
    ]);
    BlockedDate::factory()->partialDay()->create([
        'date' => $date->toDateString(),
        'open_time' => '10:00',
        'close_time' => '11:30',
    ]);

    $slots = resolve(PickupSlotResolver::class)->availableSlots($date->toDateString());

    expect($slots)->toBe(['10:00', '10:30', '11:00']);
});

test('special hours still drop slots that are at the per-window cap', function () {
    settings([
        'pickup_slots_enabled' => true,
        'pickup_slot_interval_minutes' => 30,
        'pickup_slot_max_per_window' => 1,
    ]);
    $date = Date::parse('2026-05-04');
    BusinessSchedule::factory()->create([
        'day_of_week' => $date->dayOfWeek,
        'is_open' => true,
        'open_time' => '08:00',
        'close_time' => '12:00',
    ]);
    BlockedDate::factory()->partialDay()->create([
        'date' => $date->toDateString(),
        'open_time' => '10:00',
        'close_time' => '11:00',
    ]);
    Order::factory()->confirmed()->create([
        'delivery_date' => $date->toDateString(),
        'delivery_time' => '10:00',
        'delivery_type' => DeliveryType::Pickup->value,
    ]);

    $slots = resolve(PickupSlotResolver::class)->availableSlots($date->toDateString());

    expect($slots)->toBe(['10:30']);
});

test('other dates and other kinds of blocked date keep the schedule hours', function (bool $allDay, string $blockedOn) {
    settings([
        'pickup_slots_enabled' => true,
        'pickup_slot_interval_minutes' => 30,
        'pickup_slot_max_per_window' => 3,
    ]);
    $date = Date::parse('2026-05-04');
    BusinessSchedule::factory()->create([
        'day_of_week' => $date->dayOfWeek,
        'is_open' => true,
        'open_time' => '08:00',
        'close_time' => '09:30',
    ]);
    BlockedDate::factory()->create([
        'date' => $blockedOn,
        'is_all_day' => $allDay,
        'open_time' => '10:00',
        'close_time' => '11:30',
    ]);

    $slots = resolve(PickupSlotResolver::class)->availableSlots($date->toDateString());

    expect($slots)->toBe(['08:00', '08:30', '09:00']);
})->with([
    'a partial-day block on another date' => [false, '2026-05-05'],
    'an all-day block with leftover times' => [true, '2026-05-04'],
]);

test('a partial-day block without special hours keeps the schedule hours', function () {
    settings([
        'pickup_slots_enabled' => true,
        'pickup_slot_interval_minutes' => 30,
        'pickup_slot_max_per_window' => 3,
    ]);
    $date = Date::parse('2026-05-04');
    BusinessSchedule::factory()->create([
        'day_of_week' => $date->dayOfWeek,
        'is_open' => true,
        'open_time' => '08:00',
        'close_time' => '09:00',
    ]);
    BlockedDate::factory()->create([
        'date' => $date->toDateString(),
        'is_all_day' => false,
        'open_time' => null,
        'close_time' => null,
    ]);

    $slots = resolve(PickupSlotResolver::class)->availableSlots($date->toDateString());

    expect($slots)->toBe(['08:00', '08:30']);
});

test('special hours do not open a day the schedule has closed', function () {
    settings(['pickup_slots_enabled' => true]);
    $date = Date::parse('2026-05-04');
    BusinessSchedule::factory()->create([
        'day_of_week' => $date->dayOfWeek,
        'is_open' => false,
        'open_time' => '08:00',
        'close_time' => '12:00',
    ]);
    BlockedDate::factory()->partialDay()->create(['date' => $date->toDateString()]);

    expect(resolve(PickupSlotResolver::class)->availableSlots($date->toDateString()))->toBeEmpty();
});

describe('same-day slots', function () {
    beforeEach(function () {
        // Monday 2026-05-04 11:10 in New York (15:10 UTC).
        Date::setTestNow('2026-05-04 15:10:00');
        app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings([
            'timezone' => 'America/New_York',
            'pickupSlotsEnabled' => true,
            'pickupSlotIntervalMinutes' => 30,
            'pickupSlotMaxPerWindow' => 3,
        ])));
        BusinessSchedule::factory()->create([
            'day_of_week' => 1,
            'is_open' => true,
            'open_time' => '09:00',
            'close_time' => '13:00',
        ]);
    });

    test('drops slots that have already passed in bakery time when the date is today', function () {
        $slots = resolve(PickupSlotResolver::class)->availableSlots('2026-05-04');

        expect($slots)->toBe(['11:30', '12:00', '12:30']);
    });

    test('keeps a slot that starts exactly now', function () {
        Date::setTestNow('2026-05-04 15:00:00');

        $slots = resolve(PickupSlotResolver::class)->availableSlots('2026-05-04');

        expect($slots)->toBe(['11:00', '11:30', '12:00', '12:30']);
    });

    test('offers every slot on a future date', function () {
        $slots = resolve(PickupSlotResolver::class)->availableSlots('2026-05-11');

        expect($slots)->toBe(['09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '12:30']);
    });

    test('uses the bakery day, not the UTC day, to decide what today is', function () {
        // 2026-05-05 02:00 UTC is still Monday evening 22:00 in New York: Tuesday is not today.
        Date::setTestNow('2026-05-05 02:00:00');
        BusinessSchedule::factory()->create([
            'day_of_week' => 2,
            'is_open' => true,
            'open_time' => '09:00',
            'close_time' => '10:00',
        ]);

        $slots = resolve(PickupSlotResolver::class)->availableSlots('2026-05-05');

        expect($slots)->toBe(['09:00', '09:30']);
    });
});
