<?php

use App\DataTransferObjects\Platform\WeeklyDigestData;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use App\Services\Reporting\WeeklyDigestDataCollector;
use App\Services\Settings\TenantSettings;
use App\ValueObjects\Money;

beforeEach(function () {
    setUpTenantTest();
    User::factory()->owner()->create();
});

it('returns expected data keys', function () {
    $collector = new WeeklyDigestDataCollector(TenantSettings::resolve());
    $data = $collector->collect();

    expect($data)->toBeInstanceOf(WeeklyDigestData::class)
        ->and($data->stats)->toHaveKeys(['total_orders', 'total_revenue', 'new_customers', 'avg_order_value'])
        ->and($data->stats['total_revenue'])->toEqual(Money::zero())
        ->and($data->stats['avg_order_value'])->toEqual(Money::zero());
});

it('keeps weekly revenue and average order value as money', function () {
    $this->travelTo(now()->setDate(2026, 9, 16)->setTime(12, 0));

    Order::factory()->create([
        'total' => 12.34,
        'created_at' => '2026-09-09 11:00:00',
    ]);

    Order::factory()->create([
        'total' => 20.00,
        'created_at' => '2026-09-10 11:00:00',
    ]);

    $data = (new WeeklyDigestDataCollector(TenantSettings::resolve()))->collect();

    expect($data->stats['total_revenue'])->toEqual(Money::fromDollars(32.34))
        ->and($data->stats['avg_order_value'])->toEqual(Money::fromDollars(16.17));
});
