<?php

use App\Models\Operations\BusinessSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('forDay returns the schedule for the given day', function () {
    BusinessSchedule::factory()->create(['day_of_week' => 1, 'is_open' => true]);

    $schedule = BusinessSchedule::query()->forDay(1)->firstOrFail();

    expect($schedule)->not->toBeNull()->and($schedule->day_of_week)->toBe(1);
});

test('forDay returns null when no schedule exists for the day', function () {
    expect(BusinessSchedule::query()->forDay(3)->first())->toBeNull();
});
