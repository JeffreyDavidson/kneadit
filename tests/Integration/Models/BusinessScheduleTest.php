<?php

use App\Models\Operations\BusinessSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('day of week is cast to integer', function () {
    $schedule = BusinessSchedule::factory()->create(['day_of_week' => 3]);

    expect($schedule->fresh()->day_of_week)->toBeInt();
});

test('is open is cast to boolean', function () {
    $schedule = BusinessSchedule::factory()->open()->create();

    expect($schedule->fresh()->is_open)->toBeBool();
});

test('max orders is cast to integer', function () {
    $schedule = BusinessSchedule::factory()->create(['max_orders' => 50]);

    expect($schedule->fresh()->max_orders)->toBeInt();
});

test('day name accessor returns correct day for each day of week', function (int $dayOfWeek, string $expectedName) {
    $schedule = BusinessSchedule::factory()->create(['day_of_week' => $dayOfWeek]);

    expect($schedule->day_name)->toBe($expectedName);
})->with([
    [0, 'Sunday'],
    [1, 'Monday'],
    [2, 'Tuesday'],
    [3, 'Wednesday'],
    [4, 'Thursday'],
    [5, 'Friday'],
    [6, 'Saturday'],
]);

test('day name accessor returns unknown for invalid day', function () {
    $schedule = BusinessSchedule::factory()->create(['day_of_week' => 9]);

    expect($schedule->day_name)->toBe('Unknown');
});
