<?php

use App\Models\Operations\BusinessSchedule;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();
});

test('availability endpoint returns json', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/availability');

    $response->assertOk();
    $response->assertJsonIsArray('data');
});

test('blocked dates show as unavailable', function () {
    $tomorrow = today()->addDay();

    BusinessSchedule::factory()->create([
        'day_of_week' => (int) $tomorrow->dayOfWeek,
    ]);

    // Insert directly to avoid Eloquent date cast adding time component in SQLite
    DB::table('blocked_dates')->insert([
        'date' => $tomorrow->toDateString(),
        'reason' => 'Holiday',
        'is_all_day' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/availability');

    $response->assertOk();

    $response->assertJsonFragment([
        'date' => $tomorrow->toDateString(),
        'available' => false,
    ]);
});

test('closed days show as unavailable', function () {
    $target = today();
    for ($i = 0; $i < 30; $i++) {
        $candidate = today()->addDays($i);
        if ((int) $candidate->dayOfWeek === 0) {
            $target = $candidate;
            break;
        }
    }

    BusinessSchedule::factory()->closed()->create([
        'day_of_week' => 0,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/availability');

    $response->assertJsonFragment([
        'date' => $target->toDateString(),
        'available' => false,
        'reason' => 'Closed',
    ]);
});

test('open days show as available', function () {
    $tomorrow = today()->addDay();

    BusinessSchedule::factory()->create([
        'day_of_week' => (int) $tomorrow->dayOfWeek,
        'max_orders' => 50,
    ]);

    settings(['default_daily_capacity' => '100']);

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/availability');

    $response->assertJsonFragment([
        'date' => $tomorrow->toDateString(),
        'available' => true,
    ]);
});

test('capacity is reflected in availability response', function () {
    $tomorrow = today()->addDay();

    BusinessSchedule::factory()->create([
        'day_of_week' => (int) $tomorrow->dayOfWeek,
        'max_orders' => 10,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/availability');

    $response->assertJsonFragment([
        'date' => $tomorrow->toDateString(),
        'remaining_capacity' => 10,
    ]);
});
