<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\StaffInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class StaffInvitationTest extends TestCase
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

    public function test_staff_invitation_model_exists(): void
    {
        $this->assertTrue(class_exists(StaffInvitation::class));
    }

    public function test_invitation_can_be_created(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $invitation = StaffInvitation::create([
            'email' => 'staff@example.com',
            'role' => 'staff',
            'token' => Str::random(32),
            'expires_at' => now()->addDays(7),
            'invited_by' => $owner->id,
        ]);

        $this->assertDatabaseHas('staff_invitations', ['email' => 'staff@example.com']);
    }

    public function test_invitation_is_expired_when_past_expiry(): void
    {
        $invitation = StaffInvitation::create([
            'email' => 'staff@example.com',
            'role' => 'staff',
            'token' => Str::random(32),
            'expires_at' => now()->subDay(),
            'invited_by' => User::factory()->create(['role' => 'owner'])->id,
        ]);

        $this->assertTrue($invitation->isExpired());
    }

    public function test_invitation_is_pending_when_not_accepted_and_not_expired(): void
    {
        $invitation = StaffInvitation::create([
            'email' => 'staff@example.com',
            'role' => 'staff',
            'token' => Str::random(32),
            'expires_at' => now()->addDays(7),
            'invited_by' => User::factory()->create(['role' => 'owner'])->id,
        ]);

        $this->assertTrue($invitation->isPending());
    }

    public function test_invitation_is_not_pending_when_accepted(): void
    {
        $invitation = StaffInvitation::create([
            'email' => 'staff@example.com',
            'role' => 'staff',
            'token' => Str::random(32),
            'expires_at' => now()->addDays(7),
            'accepted_at' => now(),
            'invited_by' => User::factory()->create(['role' => 'owner'])->id,
        ]);

        $this->assertFalse($invitation->isPending());
    }

    public function test_invitation_belongs_to_inviter(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $invitation = StaffInvitation::create([
            'email' => 'staff@example.com',
            'role' => 'staff',
            'token' => Str::random(32),
            'expires_at' => now()->addDays(7),
            'invited_by' => $owner->id,
        ]);

        $this->assertEquals($owner->id, $invitation->inviter->id);
    }
}
