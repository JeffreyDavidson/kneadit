<?php

use App\Enums\Orders\PaymentStatus;
use App\Models\Orders\Order;
use App\Models\Platform\Tenant;
use App\Queries\Platform\TenantOverviewQuery;
use App\Services\Tenants\TenancyManager;
use JMac\Testing\Double;

beforeEach(fn () => setUpCentralTest());

test('returns the tenant overview metrics required by the admin page', function () {
    $tenant = Tenant::factory()->create();

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->resolves(fn (Tenant $receivedTenant, callable $callback): mixed => $callback($receivedTenant));

    app()->instance(TenancyManager::class, $tenancyManager);

    $overview = resolve(TenantOverviewQuery::class)->adminStats($tenant);

    expect($overview)
        ->toBe([
            'products' => 0,
            'orders' => 0,
            'revenue' => 0.0,
            'customers' => 0,
            'reviews' => 0,
            'last_order' => null,
        ]);
});

test('the admin revenue counts only revenue orders: paid and not cancelled', function () {
    $tenant = Tenant::factory()->create();
    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->resolves(fn (Tenant $receivedTenant, callable $callback): mixed => $callback($receivedTenant));
    app()->instance(TenancyManager::class, $tenancyManager);

    Order::factory()->paid()->create(['total' => 100, 'delivery_date' => '2026-09-10']);
    Order::factory()->create(['total' => 70, 'payment_status' => PaymentStatus::Refunded, 'delivery_date' => '2026-09-10']);
    Order::factory()->paid()->cancelled()->create(['total' => 30, 'delivery_date' => '2026-09-10']);
    Order::factory()->unpaid()->create(['total' => 20, 'delivery_date' => '2026-09-10']);

    expect(resolve(TenantOverviewQuery::class)->adminStats($tenant)['revenue'])->toBe(100.0);
});
