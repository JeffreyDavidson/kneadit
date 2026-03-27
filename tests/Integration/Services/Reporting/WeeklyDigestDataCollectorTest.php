<?php

use App\Models\User;
use App\Services\Reporting\WeeklyDigestDataCollector;

beforeEach(function () {
    setUpTenantTest();
    User::query()->create(['name' => 'Test', 'email' => 'test@test.com', 'password' => bcrypt('password')]);
});

it('returns expected data keys', function () {
    $collector = new WeeklyDigestDataCollector;
    $data = $collector->collect();

    expect($data)->toHaveKeys(['stats', 'topProducts', 'atRiskCustomers', 'upcomingCount', 'storeName', 'adminUrl']);
    expect($data['stats'])->toHaveKeys(['total_orders', 'total_revenue', 'new_customers', 'avg_order_value']);
});
