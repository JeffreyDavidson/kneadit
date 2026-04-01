<?php

use App\Models\Operations\BusinessSchedule;
use App\Services\Scheduling\ScheduleService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
});

test('forDay returns schedule for a given day', function () {
    BusinessSchedule::factory()->create(['day_of_week' => 1, 'is_open' => true]);

    $schedule = resolve(ScheduleService::class)->forDay(1);

    expect($schedule)->not->toBeNull()->and($schedule->day_of_week)->toBe(1);
});

test('isOpenOn returns true when schedule is open', function () {
    BusinessSchedule::factory()->create(['day_of_week' => 2, 'is_open' => true]);

    expect(resolve(ScheduleService::class)->isOpenOn(2))->toBeTrue();
});
