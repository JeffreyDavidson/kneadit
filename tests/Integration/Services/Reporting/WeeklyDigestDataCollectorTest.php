<?php

use App\Models\Staff\User;
use App\Services\Reporting\WeeklyDigestDataCollector;
use App\Services\Settings\TenantSettings;

beforeEach(function () {
    setUpTenantTest();
    User::factory()->owner()->create();
});

it('returns expected data keys', function () {
    $collector = new WeeklyDigestDataCollector(TenantSettings::resolve());
    $data = $collector->collect();

    expect($data)->toHaveKeys(['stats', 'topProducts', 'atRiskCustomers', 'upcomingCount', 'storeName', 'adminUrl'])->and($data['stats'])->toHaveKeys(['total_orders', 'total_revenue', 'new_customers', 'avg_order_value']);
});
