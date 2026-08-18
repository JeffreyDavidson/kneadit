<?php

use App\Models\Operations\BusinessSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

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
