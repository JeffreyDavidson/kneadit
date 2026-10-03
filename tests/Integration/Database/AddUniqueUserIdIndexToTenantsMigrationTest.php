<?php

use App\Models\Staff\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

const TENANT_USER_ID_INDEX = 'tenants_user_id_unique';

beforeEach(function () {
    setUpCentralTest();
    DB::statement('DROP INDEX IF EXISTS '.TENANT_USER_ID_INDEX);
});

function runUniqueTenantOwnerMigration(): void
{
    $migration = require database_path('migrations/2026_10_02_120000_add_unique_user_id_index_to_tenants_table.php');

    throw_unless($migration instanceof Migration, RuntimeException::class, 'Expected a Laravel migration.');

    $up = [$migration, 'up'];

    throw_unless(is_callable($up), RuntimeException::class, 'Expected a runnable Laravel migration.');

    $up();
}

function tenantOwnerIndexExists(): bool
{
    return collect(Schema::getIndexes('tenants'))
        ->contains(fn (array $index): bool => $index['name'] === TENANT_USER_ID_INDEX && $index['unique']);
}

test('adds a unique index on tenants.user_id', function () {
    runUniqueTenantOwnerMigration();

    expect(tenantOwnerIndexExists())->toBeTrue();
});

test('still allows many tenants without an owner', function () {
    createTenant(['id' => 'demo-one']);
    createTenant(['id' => 'demo-two']);

    runUniqueTenantOwnerMigration();

    expect(DB::table('tenants')->whereNull('user_id')->count())->toBe(2);
});

test('rejects a second tenant for the same owner once applied', function () {
    $user = User::factory()->owner()->create();
    runUniqueTenantOwnerMigration();
    createTenant(['id' => 'first', 'user_id' => $user->id]);

    expect(fn () => createTenant(['id' => 'second', 'user_id' => $user->id]))
        ->toThrow(QueryException::class);
});

test('fails clearly and adds nothing when a user already owns more than one tenant', function () {
    $logger = Log::spy();
    $user = User::factory()->owner()->create();
    createTenant(['id' => 'first', 'user_id' => $user->id]);
    createTenant(['id' => 'second', 'user_id' => $user->id]);

    expect(fn () => runUniqueTenantOwnerMigration())
        ->toThrow(RuntimeException::class, 'own more than one bakery')
        ->and(tenantOwnerIndexExists())->toBeFalse()
        ->and(DB::table('tenants')->count())->toBe(2);

    $logger->shouldHaveReceived('warning')->once();
});

test('links an unowned tenant to the one user with the same email, ignoring case', function () {
    $user = User::factory()->owner()->create(['email' => 'baker@example.com']);
    createTenant(['id' => 'unlinked', 'email' => 'Baker@Example.com']);

    runUniqueTenantOwnerMigration();

    expect(DB::table('tenants')->where('id', 'unlinked')->value('user_id'))->toBe($user->id);
});

test('leaves ambiguous and unmatched tenants unlinked and logs them', function () {
    $logger = Log::spy();
    User::factory()->owner()->create(['email' => 'shared@example.com']);
    DB::table('users')->insert([
        'name' => 'Twin',
        'email' => 'SHARED@example.com',
        'password' => 'x',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    createTenant(['id' => 'ambiguous', 'email' => 'shared@example.com']);
    createTenant(['id' => 'nobody', 'email' => 'nobody@example.com']);

    runUniqueTenantOwnerMigration();

    expect(DB::table('tenants')->whereNull('user_id')->pluck('id')->sort()->values()->all())->toBe(['ambiguous', 'nobody'])
        ->and(tenantOwnerIndexExists())->toBeTrue();

    $logger->shouldHaveReceived('warning')->once();
});

test('does not overwrite an existing owner', function () {
    $owner = User::factory()->owner()->create(['email' => 'owner@example.com']);
    User::factory()->owner()->create(['email' => 'other@example.com']);
    createTenant(['id' => 'owned', 'email' => 'other@example.com', 'user_id' => $owner->id]);

    runUniqueTenantOwnerMigration();

    expect(DB::table('tenants')->where('id', 'owned')->value('user_id'))->toBe($owner->id);
});
