<?php

use App\Enums\Orders\DeliveryType;
use App\Models\Operations\BusinessSchedule;
use App\Models\Orders\Order;
use App\Services\Scheduling\PickupSlotResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('returns empty when feature is disabled', function () {
    settings(['pickup_slots_enabled' => false]);
    $date = Illuminate\Support\Facades\Date::parse('2026-05-04'); // Monday

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
    $date = Illuminate\Support\Facades\Date::parse('2026-05-04');

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
    $date = Illuminate\Support\Facades\Date::parse('2026-05-04');

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
    $date = Illuminate\Support\Facades\Date::parse('2026-05-04');

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
    $date = Illuminate\Support\Facades\Date::parse('2026-05-04');

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
    $date = Illuminate\Support\Facades\Date::parse('2026-05-04');

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
