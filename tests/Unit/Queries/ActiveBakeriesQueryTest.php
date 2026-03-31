<?php

use App\Queries\ActiveBakeriesQuery;

beforeEach(function () {
    config(['database.connections.central' => config('database.connections.sqlite')]);
    test()->artisan('migrate:fresh');
    createCentralTables();
    Illuminate\Support\Facades\DB::purge('central');
    $pdo = Illuminate\Support\Facades\DB::connection('sqlite')->getPdo();
    Illuminate\Support\Facades\DB::connection('central')->setPdo($pdo)->setReadPdo($pdo);
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
