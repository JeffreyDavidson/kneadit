<?php

use App\Models\Operations\BusinessSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\withoutMiddleware;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('endpoint returns available slots for a date when enabled', function () {
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
        'close_time' => '09:00',
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson("/pickup-slots/{$date->toDateString()}");

    $response->assertOk();
    expect($response->json('data.slots'))->toBe(['08:00', '08:30']);
});

test('endpoint returns empty slots when feature is disabled', function () {
    settings(['pickup_slots_enabled' => false]);

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/pickup-slots/2026-05-04');

    $response->assertOk();
    expect($response->json('data.slots'))->toBeEmpty();
});

test('endpoint rejects malformed dates', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/pickup-slots/not-a-date');

    $response->assertNotFound();
});
