<?php

use App\Queries\Platform\ActiveBakeriesQuery;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    config(['database.connections.central' => config('database.connections.sqlite')]);
    test()->artisan('migrate:fresh');
    createCentralTables();
    DB::purge('central');
    $pdo = DB::connection('sqlite')->getPdo();
    DB::connection('central')->setPdo($pdo)->setReadPdo($pdo);
});

test('it leaves out paused bakeries', function () {
    createTenant(['id' => 'running-bakery', 'store_name' => 'Running']);
    createTenant(['id' => 'paused-bakery', 'store_name' => 'Paused', 'paused_at' => now()]);

    expect(ActiveBakeriesQuery::get()->pluck('name')->all())->toBe(['Running']);
});

test('it returns only unpaused tenants with storefronts enabled, whatever the retired is_active column holds', function () {
    createTenant([
        'id' => 'active-bakery',
        'name' => 'Active Bakery',
        'store_name' => 'Sweet Treats',
        'storefront_enabled' => true,
    ]);

    createTenant([
        'id' => 'old-deactivated-bakery',
        'name' => 'Old Deactivated Bakery',
        'store_name' => 'Old Deactivated',
        'is_active' => false,
        'storefront_enabled' => true,
    ]);

    createTenant([
        'id' => 'no-storefront',
        'name' => 'No Storefront',
        'storefront_enabled' => false,
    ]);

    $bakeries = ActiveBakeriesQuery::get();

    expect($bakeries->pluck('name')->all())->toBe(['Sweet Treats', 'Old Deactivated']);
});

test('it links each bakery by its subdomain, or its verified custom domain', function () {
    config(['app.url' => 'https://app.getkneadit.app', 'tenancy.tenant_domain' => 'getkneadit.app']);
    createTenant(['id' => 'bakery-on-biscotto', 'store_name' => 'Biscotto', 'subdomain' => 'bakeryonbiscotto']);
    createTenant([
        'id' => 'custom-bakery',
        'email' => 'custom@example.com',
        'store_name' => 'Custom',
        'custom_domain' => 'shop.example.com',
        'custom_domain_verified_at' => now(),
    ]);
    DB::table('domains')->insert([
        'domain' => 'shop.example.com',
        'tenant_id' => 'custom-bakery',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(ActiveBakeriesQuery::get()->pluck('url', 'name')->all())->toBe([
        'Biscotto' => 'https://bakeryonbiscotto.getkneadit.app',
        'Custom' => 'https://shop.example.com',
    ]);
});
