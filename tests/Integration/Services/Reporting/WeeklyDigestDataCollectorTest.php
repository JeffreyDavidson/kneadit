<?php

use App\DataTransferObjects\Platform\WeeklyDigestData;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use App\Services\Reporting\WeeklyDigestDataCollector;
use App\Services\Settings\TenantSettings;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    setUpTenantTest();
    User::factory()->owner()->create();
});

it('returns expected data keys', function () {
    $collector = new WeeklyDigestDataCollector(TenantSettings::resolve());
    $data = $collector->collect();

    expect($data)->toBeInstanceOf(WeeklyDigestData::class)
        ->and($data->stats)->toHaveKeys(['total_orders', 'total_revenue', 'new_customers', 'avg_order_value'])
        ->and($data->stats['total_orders'])->toBe(0)
        ->and($data->stats['total_revenue'])->toEqual(Money::zero())
        ->and($data->stats['avg_order_value'])->toEqual(Money::zero());
});

it('aggregates weekly orders and revenue without filtering cancelled orders', function () {
    $this->travelTo(now()->setDate(2026, 9, 16)->setTime(12, 0));

    Order::factory()->create([
        'total' => 12.34,
        'created_at' => '2026-09-09 11:00:00',
    ]);

    Order::factory()->create([
        'total' => 20.00,
        'created_at' => '2026-09-10 11:00:00',
    ]);

    Order::factory()->cancelled()->create([
        'total' => 9.99,
        'created_at' => '2026-09-11 11:00:00',
    ]);

    DB::connection()->flushQueryLog();
    DB::connection()->enableQueryLog();

    try {
        $data = new WeeklyDigestDataCollector(TenantSettings::resolve())->collect();
    } finally {
        DB::connection()->disableQueryLog();
    }

    $orderAggregateQueries = collect(DB::connection()->getQueryLog())
        ->filter(function (array $query): bool {
            $sql = strtolower($query['query']);

            return str_contains($sql, 'orders')
                && str_contains($sql, 'created_at')
                && str_contains($sql, 'count(')
                && str_contains($sql, 'sum(');
        });

    expect($data->stats['total_orders'])->toBe(3)
        ->and($data->stats['total_revenue'])->toEqual(Money::fromDollars(42.33))
        ->and($data->stats['avg_order_value'])->toEqual(Money::fromDollars(14.11))
        ->and($orderAggregateQueries)->toHaveCount(1);
});
