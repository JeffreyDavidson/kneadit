<?php

use App\Models\Platform\Tenant;
use App\Queries\Platform\TenantDataCountsQuery;
use App\Services\Tenants\TenancyManager;
use Illuminate\Support\Facades\DB;
use JMac\Testing\Double;

beforeEach(fn () => setUpCentralTest());

test('counts tenant data inside the tenant context', function () {
    $tenant = Tenant::factory()->create();

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->resolves(fn (Tenant $tenant, callable $callback): mixed => $callback($tenant));

    app()->instance(TenancyManager::class, $tenancyManager);

    expect(resolve(TenantDataCountsQuery::class)->forTenant($tenant))
        ->toBe([
            'products' => 0,
            'categories' => 0,
            'orders' => 0,
            'customers' => 0,
            'reviews' => 0,
        ]);
});

test('counts customers separately from staff users', function () {
    $tenant = Tenant::factory()->create();
    DB::table('users')->insert([
        'name' => 'Staff Member',
        'email' => 'staff@example.com',
        'password' => 'hashed-password',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('customers')->insert([
        'name' => 'Customer',
        'email' => 'customer@example.com',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->resolves(fn (Tenant $tenant, callable $callback): mixed => $callback($tenant));

    app()->instance(TenancyManager::class, $tenancyManager);

    expect(resolve(TenantDataCountsQuery::class)->forTenant($tenant)['customers'])->toBe(1);
});
