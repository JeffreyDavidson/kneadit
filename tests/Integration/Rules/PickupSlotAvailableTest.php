<?php

use App\Enums\Orders\DeliveryType;
use App\Models\Operations\BusinessSchedule;
use App\Models\Orders\Order;
use App\Rules\PickupSlotAvailable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\MessageBag;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();

    test()->date = Date::parse('2026-05-04'); // Monday

    BusinessSchedule::factory()->create([
        'day_of_week' => test()->date->dayOfWeek,
        'is_open' => true,
        'open_time' => '08:00',
        'close_time' => '10:00',
    ]);
});

/**
 * @param  array<string, mixed>  $overrides
 */
function pickupSlotErrors(array $overrides = []): MessageBag
{
    return validator(
        array_merge([
            'delivery_type' => DeliveryType::Pickup->value,
            'delivery_date' => '2026-05-04',
            'delivery_time' => '08:00',
        ], $overrides),
        ['delivery_time' => ['nullable', 'string', 'max:20', new PickupSlotAvailable]],
    )->errors();
}

test('a pickup time is checked against the open slots when slots are enabled', function (?string $time, int $booked, bool $passes) {
    settings([
        'pickup_slots_enabled' => true,
        'pickup_slot_interval_minutes' => 30,
        'pickup_slot_max_per_window' => 2,
    ]);
    Order::factory()->confirmed()->count($booked)->create([
        'delivery_date' => test()->date->toDateString(),
        'delivery_time' => '08:30',
        'delivery_type' => DeliveryType::Pickup->value,
    ]);

    $errors = pickupSlotErrors(['delivery_time' => $time]);

    expect($errors->has('delivery_time'))->toBe(! $passes);
})->with([
    'an open slot' => ['08:00', 0, true],
    'a slot with room left' => ['08:30', 1, true],
    'a full slot' => ['08:30', 2, false],
    'a time before opening' => ['07:30', 0, false],
    'a time at closing' => ['10:00', 0, false],
    'a time between slots' => ['08:15', 0, false],
    'a missing time' => [null, 0, false],
    'an empty time' => ['', 0, false],
]);

test('the error asks the customer to choose another time', function () {
    settings(['pickup_slots_enabled' => true]);

    $errors = pickupSlotErrors(['delivery_time' => '23:00']);

    expect($errors->first('delivery_time'))->toBe('That pickup time is no longer available. Please choose another.');
});

test('the rule does nothing when pickup slots are disabled', function (?string $time) {
    settings(['pickup_slots_enabled' => false]);

    $errors = pickupSlotErrors(['delivery_time' => $time]);

    expect($errors->has('delivery_time'))->toBeFalse();
})->with([
    'a missing time' => [null],
    'free text' => ['around lunch'],
    'a time outside hours' => ['23:00'],
]);

test('the rule ignores delivery orders', function (?string $time) {
    settings(['pickup_slots_enabled' => true]);

    $errors = pickupSlotErrors([
        'delivery_type' => DeliveryType::Delivery->value,
        'delivery_time' => $time,
    ]);

    expect($errors->has('delivery_time'))->toBeFalse();
})->with([
    'a missing time' => [null],
    'free text' => ['around lunch'],
]);

test('the rule leaves an unusable delivery date to the date rules', function (?string $date) {
    settings(['pickup_slots_enabled' => true]);

    $errors = pickupSlotErrors(['delivery_date' => $date]);

    expect($errors->has('delivery_time'))->toBeFalse();
})->with([
    'a missing date' => [null],
    'an unparseable date' => ['not-a-date'],
]);
