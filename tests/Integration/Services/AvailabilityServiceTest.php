<?php

use App\Models\Operations\BlockedDate;
use App\Models\Operations\BusinessSchedule;
use App\Models\Operations\Holiday;
use App\Models\Orders\Order;
use App\Services\Scheduling\AvailabilityService;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('returns availability for next 30 days', function () {
    $result = resolve(AvailabilityService::class)->getAvailability();

    expect($result)->toBeArray()
        ->toHaveCount(30)
        ->and($result[0])->toHaveKeys(['date', 'available', 'reason', 'remaining_capacity']);
});

test('blocked date shows as unavailable', function () {
    BlockedDate::factory()->create([
        'date' => now()->addDay()->toDateString(),
        'is_all_day' => true,
        'reason' => 'Holiday',
    ]);

    $result = resolve(AvailabilityService::class)->getAvailability();
    $tomorrow = collect($result)->firstWhere('date', now()->addDay()->toDateString());

    expect($tomorrow['available'])->toBeFalse()
        ->and($tomorrow['remaining_capacity'])->toBe(0);
});

test('counts active orders once for the requested date window', function () {
    $today = now()->startOfDay();

    foreach (range(0, 2) as $offset) {
        $date = $today->copy()->addDays($offset);

        BusinessSchedule::factory()->create([
            'day_of_week' => $date->dayOfWeek,
            'max_orders' => 2,
        ]);
    }

    Order::factory()->create(['delivery_date' => $today->toDateString()]);
    Order::factory()->cancelled()->create(['delivery_date' => $today->toDateString()]);
    Order::factory()->count(2)->create(['delivery_date' => $today->copy()->addDay()->toDateString()]);

    $orderQueries = [];
    DB::listen(function (QueryExecuted $query) use (&$orderQueries): void {
        $sql = strtolower(str_replace(['"', '`', '[', ']'], '', $query->sql));

        if (str_starts_with(ltrim($sql), 'select') && str_contains($sql, 'from orders')) {
            $orderQueries[] = $sql;
        }
    });

    $result = resolve(AvailabilityService::class)->getAvailability(3);

    expect($result[0]['remaining_capacity'])->toBe(1)
        ->and($result[0]['available'])->toBeTrue()
        ->and($result[1]['remaining_capacity'])->toBe(0)
        ->and($result[1]['available'])->toBeFalse()
        ->and($result[1]['reason'])->toBe('Fully booked')
        ->and($result[2]['remaining_capacity'])->toBe(2)
        ->and($orderQueries)->toHaveCount(1);
});

test('holiday past its order deadline shows as unavailable', function () {
    Date::setTestNow('2026-12-21 09:00');
    Holiday::factory()->active()->create([
        'name' => 'Christmas',
        'date' => '2026-12-25',
        'order_deadline' => '2026-12-20',
    ]);

    $christmas = collect(resolve(AvailabilityService::class)->getAvailability(7))->firstWhere('date', '2026-12-25');

    expect($christmas)
        ->available->toBeFalse()
        ->reason->toBe('Orders closed for Christmas')
        ->remaining_capacity->toBe(0);
});
