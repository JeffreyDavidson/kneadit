<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    setUpCentralTest();
});

function runAddSubdomainToTenantsMigration(): void
{
    $migration = require database_path('migrations/2026_10_05_120000_add_subdomain_to_tenants_table.php');

    throw_unless($migration instanceof Migration, RuntimeException::class, 'Expected a Laravel migration.');

    $up = [$migration, 'up'];

    throw_unless(is_callable($up), RuntimeException::class, 'Expected a runnable Laravel migration.');

    $up();
}

function addDomainRow(string $tenantId, string $domain): void
{
    DB::table('domains')->insert([
        'domain' => $domain,
        'tenant_id' => $tenantId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

test('adds a nullable subdomain column to tenants', function () {
    expect(Schema::hasColumn('tenants', 'subdomain'))->toBeTrue();
});

test('backfills the subdomain from the dot-less domain row, or the id when there is not exactly one', function () {
    createTenant(['id' => 'bakery-on-biscotto']);
    addDomainRow('bakery-on-biscotto', 'bakery-on-biscotto');
    addDomainRow('bakery-on-biscotto', 'bakeryonbiscotto.com');
    createTenant(['id' => 'no-row-bakery', 'email' => 'a@example.com']);
    addDomainRow('no-row-bakery', 'only.custom.example.com');
    createTenant(['id' => 'two-rows', 'email' => 'b@example.com']);
    addDomainRow('two-rows', 'first');
    addDomainRow('two-rows', 'second');
    createTenant(['id' => 'already-set', 'email' => 'c@example.com', 'subdomain' => 'keepme']);
    addDomainRow('already-set', 'already-set');

    runAddSubdomainToTenantsMigration();

    expect(DB::table('tenants')->orderBy('id')->pluck('subdomain', 'id')->all())->toBe([
        'already-set' => 'keepme',
        'bakery-on-biscotto' => 'bakery-on-biscotto',
        'no-row-bakery' => 'no-row-bakery',
        'two-rows' => 'two-rows',
    ]);
});

test('backfills a dot-less domain row that differs from the id', function () {
    createTenant(['id' => 'legacy-id']);
    addDomainRow('legacy-id', 'shortname');

    runAddSubdomainToTenantsMigration();

    expect(DB::table('tenants')->where('id', 'legacy-id')->value('subdomain'))->toBe('shortname');
});

test('is safe to run twice', function () {
    createTenant(['id' => 'twice']);
    addDomainRow('twice', 'twice');

    runAddSubdomainToTenantsMigration();
    runAddSubdomainToTenantsMigration();

    expect(DB::table('tenants')->where('id', 'twice')->value('subdomain'))->toBe('twice');
});
