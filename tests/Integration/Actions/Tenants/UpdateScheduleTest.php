<?php

use App\Actions\Tenants\UpdateSchedule;
use App\Models\Operations\BusinessSchedule;

beforeEach(fn () => setUpTenantTest());

test('creates schedule for all seven days', function () {
    $schedule = [];
    for ($day = 0; $day <= 6; $day++) {
        $schedule[$day] = [
            'is_open' => true,
            'open_time' => '08:00',
            'close_time' => '17:00',
            'order_cutoff_time' => '15:00',
            'max_orders' => 20,
        ];
    }

    app(UpdateSchedule::class)($schedule);

    expect(BusinessSchedule::count())->toBe(7);
});

test('open day stores all fields correctly', function () {
    app(UpdateSchedule::class)([
        1 => [
            'is_open' => true,
            'open_time' => '09:00',
            'close_time' => '18:00',
            'order_cutoff_time' => '16:00',
            'max_orders' => 15,
        ],
    ]);

    $record = BusinessSchedule::where('day_of_week', 1)->first();

    expect($record)
        ->is_open->toBeTrue()
        ->open_time->toBe('09:00')
        ->close_time->toBe('18:00')
        ->order_cutoff_time->toBe('16:00')
        ->max_orders->toBe(15);
});

test('closed day nullifies time fields despite having values in input', function () {
    app(UpdateSchedule::class)([
        0 => [
            'is_open' => false,
            'open_time' => '09:00',
            'close_time' => '17:00',
            'order_cutoff_time' => '15:00',
            'max_orders' => 10,
        ],
    ]);

    $record = BusinessSchedule::where('day_of_week', 0)->first();

    expect($record)
        ->is_open->toBeFalse()
        ->open_time->toBeNull()
        ->close_time->toBeNull()
        ->order_cutoff_time->toBeNull()
        ->max_orders->toBeNull();
});

test('updates existing schedule without creating duplicates', function () {
    app(UpdateSchedule::class)([
        1 => ['is_open' => true, 'open_time' => '08:00', 'close_time' => '17:00', 'order_cutoff_time' => null, 'max_orders' => null],
    ]);

    app(UpdateSchedule::class)([
        1 => ['is_open' => true, 'open_time' => '10:00', 'close_time' => '20:00', 'order_cutoff_time' => '18:00', 'max_orders' => 25],
    ]);

    expect(BusinessSchedule::where('day_of_week', 1)->count())->toBe(1);

    $record = BusinessSchedule::where('day_of_week', 1)->first();

    expect($record)
        ->open_time->toBe('10:00')
        ->close_time->toBe('20:00')
        ->max_orders->toBe(25);
});

test('handles null optional fields', function () {
    app(UpdateSchedule::class)([
        3 => [
            'is_open' => true,
            'open_time' => '08:00',
            'close_time' => '17:00',
            'order_cutoff_time' => null,
            'max_orders' => null,
        ],
    ]);

    $record = BusinessSchedule::where('day_of_week', 3)->first();

    expect($record)
        ->is_open->toBeTrue()
        ->open_time->toBe('08:00')
        ->close_time->toBe('17:00')
        ->order_cutoff_time->toBeNull()
        ->max_orders->toBeNull();
});
