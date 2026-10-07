<?php

use App\Enums\Staff\UserRole;
use App\Models\Staff\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

beforeEach(fn () => setUpTenantTest());

function runDefaultTenantUserRoleToStaffMigration(): void
{
    $migration = require database_path('migrations/tenant/2026_10_06_120000_default_tenant_user_role_to_staff.php');

    throw_unless($migration instanceof Migration, RuntimeException::class, 'Expected a Laravel migration.');

    $up = [$migration, 'up'];

    throw_unless(is_callable($up), RuntimeException::class, 'Expected a runnable Laravel migration.');

    $up();
}

function insertUserWithoutRole(string $email): void
{
    DB::table('users')->insert([
        'name' => 'No Role',
        'email' => $email,
        'password' => 'x',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

test('a tenant user created without a role is Staff', function () {
    insertUserWithoutRole('norole@example.com');

    expect(User::query()->where('email', 'norole@example.com')->sole()->role)->toBe(UserRole::Staff);
});

test('running the migration again keeps every existing role', function () {
    User::factory()->owner()->create(['email' => 'owner@example.com']);
    User::factory()->manager()->create(['email' => 'manager@example.com']);
    User::factory()->create(['email' => 'staff@example.com', 'role' => UserRole::Staff]);

    runDefaultTenantUserRoleToStaffMigration();

    expect(User::query()->orderBy('email')->pluck('role', 'email')->all())->toBe([
        'manager@example.com' => UserRole::Manager,
        'owner@example.com' => UserRole::Owner,
        'staff@example.com' => UserRole::Staff,
    ]);
});
