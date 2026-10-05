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

test('it returns only active tenants with storefronts enabled', function () {
    createTenant([
        'id' => 'active-bakery',
        'name' => 'Active Bakery',
        'store_name' => 'Sweet Treats',
        'is_active' => true,
        'storefront_enabled' => true,
    ]);

    createTenant([
        'id' => 'inactive-bakery',
        'name' => 'Inactive Bakery',
        'is_active' => false,
        'storefront_enabled' => true,
    ]);

    createTenant([
        'id' => 'no-storefront',
        'name' => 'No Storefront',
        'is_active' => true,
        'storefront_enabled' => false,
    ]);

    $bakeries = ActiveBakeriesQuery::get();

    expect($bakeries)->toHaveCount(1)
        ->and($bakeries->first()['name'])->toBe('Sweet Treats');
});
