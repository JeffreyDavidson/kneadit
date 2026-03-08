<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.central' => config('database.connections.sqlite')]);
        $tenantMigrationPath = database_path('migrations/tenant');
        if (is_dir($tenantMigrationPath)) {
            $this->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
        }
    }

    public function test_user_model_has_role_methods(): void
    {
        $user = new User;
        $this->assertTrue(method_exists($user, 'isOwner'));
        $this->assertTrue(method_exists($user, 'isManager'));
        $this->assertTrue(method_exists($user, 'isStaff'));
        $this->assertTrue(method_exists($user, 'hasMinRole'));
    }

    public function test_is_owner_returns_true_for_owner_role(): void
    {
        $user = User::factory()->create(['role' => 'owner']);
        $this->assertTrue($user->isOwner());
    }

    public function test_is_owner_returns_false_for_manager(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        $this->assertFalse($user->isOwner());
    }

    public function test_is_owner_returns_false_for_staff(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $this->assertFalse($user->isOwner());
    }

    public function test_is_manager_returns_true_for_manager(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        $this->assertTrue($user->isManager());
    }

    public function test_is_staff_returns_true_for_staff(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $this->assertTrue($user->isStaff());
    }

    public function test_has_min_role_staff_is_true_for_all_roles(): void
    {
        foreach (['staff', 'manager', 'owner'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->assertTrue($user->hasMinRole('staff'), "{$role} should have min role staff");
        }
    }

    public function test_has_min_role_manager_is_false_for_staff(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $this->assertFalse($user->hasMinRole('manager'));
    }

    public function test_has_min_role_manager_is_true_for_manager_and_owner(): void
    {
        foreach (['manager', 'owner'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->assertTrue($user->hasMinRole('manager'), "{$role} should have min role manager");
        }
    }

    public function test_has_min_role_owner_is_true_only_for_owner(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $manager = User::factory()->create(['role' => 'manager']);
        $staff = User::factory()->create(['role' => 'staff']);

        $this->assertTrue($owner->hasMinRole('owner'));
        $this->assertFalse($manager->hasMinRole('owner'));
        $this->assertFalse($staff->hasMinRole('owner'));
    }
}
