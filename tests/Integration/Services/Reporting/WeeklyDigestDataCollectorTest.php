<?php

use App\DataTransferObjects\Platform\WeeklyDigestData;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use App\Services\Reporting\WeeklyDigestDataCollector;
use App\Services\Settings\TenantSettings;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    setUpTenantTest();
    User::factory()->owner()->create();
});

it('returns expected data keys', function () {
    $collector = resolve(WeeklyDigestDataCollector::class);
    $data = $collector->collect();

    expect($data)->toBeInstanceOf(WeeklyDigestData::class)
        ->and($data->stats)->toHaveKeys(['total_orders', 'total_revenue', 'new_customers', 'avg_order_value'])
        ->and($data->stats['total_orders'])->toBe(0)
        ->and($data->stats['total_revenue'])->toEqual(Money::zero())
        ->and($data->stats['avg_order_value'])->toEqual(Money::zero());
});

it('aggregates last week\'s revenue orders: paid, not cancelled, dated by delivery date', function () {
    $this->travelTo(now()->setDate(2026, 9, 16)->setTime(12, 0));

    Order::factory()->paid()->create([
        'total' => 12.34,
        'delivery_date' => '2026-09-09',
        'created_at' => '2026-08-01 11:00:00',
    ]);

    Order::factory()->paid()->create([
        'total' => 20.00,
        'delivery_date' => '2026-09-10',
        'created_at' => '2026-09-10 11:00:00',
    ]);

    Order::factory()->paid()->cancelled()->create([
        'total' => 9.99,
        'delivery_date' => '2026-09-11',
        'created_at' => '2026-09-11 11:00:00',
    ]);

    Order::factory()->unpaid()->create([
        'total' => 7.00,
        'delivery_date' => '2026-09-11',
        'created_at' => '2026-09-11 11:00:00',
    ]);

    Order::factory()->paid()->create([
        'total' => 99.00,
        'delivery_date' => '2026-09-14',
        'created_at' => '2026-09-10 11:00:00',
    ]);

    DB::connection()->flushQueryLog();
    DB::connection()->enableQueryLog();

    try {
        $data = resolve(WeeklyDigestDataCollector::class)->collect();
    } finally {
        DB::connection()->disableQueryLog();
    }

    $orderAggregateQueries = collect(DB::connection()->getQueryLog())
        ->filter(function (array $query): bool {
            $sql = strtolower($query['query']);

            return str_contains($sql, 'orders')
                && str_contains($sql, 'delivery_date')
                && str_contains($sql, 'count(')
                && str_contains($sql, 'sum(');
        });

    expect($data->stats['total_orders'])->toBe(2)
        ->and($data->stats['total_revenue'])->toEqual(Money::fromDollars(32.34))
        ->and($data->stats['avg_order_value'])->toEqual(Money::fromDollars(16.17))
        ->and($orderAggregateQueries)->toHaveCount(1);
});

// 2026-10-04 23:00 UTC is Monday 2026-10-05 08:00 in Tokyo, when the digest goes
// out: "last week" is the bakery-local Monday 2026-09-28 to Sunday 2026-10-04
// (2026-09-27 15:00 to 2026-10-04 14:59 UTC), and "upcoming" is 2026-10-05 to 2026-10-11.
test('the digest weeks follow the bakery-local clock, not UTC', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(
        orders: makeOrderSettings(['timezone' => 'Asia/Tokyo']),
    ));
    Date::setTestNow('2026-10-04 23:00');
    Order::factory()->paid()->create(['total' => 10.00, 'delivery_date' => '2026-09-30']);
    Order::factory()->paid()->create(['total' => 99.00, 'delivery_date' => '2026-09-23']);
    Order::factory()->paid()->create(['total' => 99.00, 'delivery_date' => '2026-10-05']);
    Customer::factory()->create(['created_at' => '2026-09-29 03:00:00']);
    Customer::factory()->create(['created_at' => '2026-10-04 20:00:00']);
    Order::factory()->create(['delivery_date' => '2026-10-06']);
    Order::factory()->create(['delivery_date' => '2026-10-02']);

    $data = resolve(WeeklyDigestDataCollector::class)->collect();

    expect($data->stats['total_orders'])->toBe(1)
        ->and($data->stats['total_revenue'])->toEqual(Money::fromDollars(10.00))
        ->and($data->stats['new_customers'])->toBe(1)
        ->and($data->upcomingCount)->toBe(2);
});
