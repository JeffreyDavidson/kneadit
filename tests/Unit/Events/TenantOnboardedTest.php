<?php

use App\Events\Platform\TenantOnboarded;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;

beforeEach(function () {
    config(['database.connections.central' => config('database.connections.sqlite')]);
    test()->artisan('migrate:fresh');
    createCentralTables();
    Illuminate\Support\Facades\DB::purge('central');
    $pdo = Illuminate\Support\Facades\DB::connection('sqlite')->getPdo();
    Illuminate\Support\Facades\DB::connection('central')->setPdo($pdo)->setReadPdo($pdo);
});

test('it can be constructed with user, tenant, and admin url', function () {
    $user = User::factory()->create();
    $tenantRecord = createTenant(['id' => 'test-bakery']);
    $tenant = Tenant::query()->find('test-bakery');

    $event = new TenantOnboarded($user, $tenant, 'https://test-bakery.kneadit.test/admin');

    expect($event->user)->toBe($user)
        ->and($event->tenant)->toBe($tenant)
        ->and($event->adminUrl)->toBe('https://test-bakery.kneadit.test/admin');
});
