<?php

use App\Models\Staff\User;
use App\Services\Reporting\WeeklyDigestDataCollector;

beforeEach(function () {
    setUpTenantTest();
    User::factory()->owner()->create();
});

it('returns expected data keys', function () {
    $collector = new WeeklyDigestDataCollector;
    $data = $collector->collect();

    expect($data->stats)->toHaveKeys(['total_orders', 'total_revenue', 'new_customers', 'avg_order_value'])
        ->and($data->topProducts)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and($data->atRiskCustomers)->toBeInstanceOf(Illuminate\Support\Collection::class);
});
