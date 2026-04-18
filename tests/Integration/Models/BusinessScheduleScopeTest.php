<?php

use App\Models\Operations\BusinessSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
});

test('forDay scope returns schedule for the given day', function () {
    BusinessSchedule::factory()->create(['day_of_week' => 1, 'is_open' => true]);

    $schedule = BusinessSchedule::query()->forDay(1)->first();

    expect($schedule)->not->toBeNull()->and($schedule->day_of_week)->toBe(1);
});

test('forDay scope returns null when no schedule exists for the day', function () {
    expect(BusinessSchedule::query()->forDay(3)->first())->toBeNull();
});
