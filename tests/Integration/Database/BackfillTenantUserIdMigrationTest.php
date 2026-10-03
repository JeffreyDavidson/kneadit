<?php

use App\Models\Staff\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

beforeEach(fn () => setUpCentralTest());

function runBackfillTenantUserIdMigration(): void
{
    $migration = require database_path('migrations/2026_10_03_015755_backfill_user_id_on_tenants_table.php');

    throw_unless($migration instanceof Migration, RuntimeException::class, 'Expected a Laravel migration.');

    $up = [$migration, 'up'];

    throw_unless(is_callable($up), RuntimeException::class, 'Expected a runnable Laravel migration.');

    $up();
}

test('links each tenant to the user whose email matches, ignoring case', function () {
    $owner = User::factory()->owner()->create(['email' => 'baker@example.com']);
    createTenant(['id' => 'matched-bakery', 'email' => 'Baker@Example.COM']);

    runBackfillTenantUserIdMigration();

    expect(DB::table('tenants')->where('id', 'matched-bakery')->value('user_id'))->toBe($owner->id);
});

test('leaves tenants without a matching user unlinked', function () {
    User::factory()->owner()->create(['email' => 'someone@example.com']);
    createTenant(['id' => 'orphan-bakery', 'email' => 'nobody@example.com']);

    runBackfillTenantUserIdMigration();

    expect(DB::table('tenants')->where('id', 'orphan-bakery')->value('user_id'))->toBeNull();
});

test('keeps an owner that is already linked', function () {
    $linked = User::factory()->owner()->create(['email' => 'linked@example.com']);
    User::factory()->owner()->create(['email' => 'shared@example.com']);
    createTenant(['id' => 'linked-bakery', 'email' => 'shared@example.com', 'user_id' => $linked->id]);

    runBackfillTenantUserIdMigration();

    expect(DB::table('tenants')->where('id', 'linked-bakery')->value('user_id'))->toBe($linked->id);
});
