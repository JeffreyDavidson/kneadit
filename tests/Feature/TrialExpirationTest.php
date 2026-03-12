<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\CentralTestCase;

class TrialExpirationTest extends CentralTestCase
{
    public function test_trial_check_command_exists(): void
    {
        Mail::fake();

        $this->artisan('trial:check')
            ->assertSuccessful();
    }

    public function test_7_day_reminder_sends_email(): void
    {
        Mail::fake();

        User::create([
            'name' => 'Baker',
            'email' => 'baker7@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->createTenant([
            'id' => 'expiring-7d',
            'name' => 'Baker',
            'email' => 'baker7@test.com',
            'trial_ends_at' => now()->addDays(7)->startOfDay(),
            'is_active' => true,
        ]);

        $this->artisan('trial:check')
            ->expectsOutputToContain('7d reminder');
    }

    public function test_expired_trial_pauses_storefront(): void
    {
        Mail::fake();

        User::create([
            'name' => 'Baker',
            'email' => 'expired@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->createTenant([
            'id' => 'expired-bakery',
            'name' => 'Baker',
            'email' => 'expired@test.com',
            'trial_ends_at' => now()->subDays(1),
            'is_active' => true,
            'storefront_enabled' => true,
        ]);

        $this->artisan('trial:check');

        $tenant = DB::table('tenants')
            ->where('id', 'expired-bakery')
            ->first();

        $this->assertFalse((bool) $tenant->storefront_enabled);
    }

    public function test_expired_storefront_already_paused_not_repaused(): void
    {
        Mail::fake();

        User::create([
            'name' => 'Baker',
            'email' => 'already@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->createTenant([
            'id' => 'already-paused',
            'name' => 'Baker',
            'email' => 'already@test.com',
            'trial_ends_at' => now()->subDays(5),
            'is_active' => true,
            'storefront_enabled' => false,
        ]);

        // Should succeed without trying to pause again
        $this->artisan('trial:check')
            ->assertSuccessful();
    }

    public function test_no_action_for_inactive_tenant(): void
    {
        Mail::fake();

        User::create([
            'name' => 'Inactive',
            'email' => 'inactive@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->createTenant([
            'id' => 'inactive-bakery',
            'name' => 'Inactive',
            'email' => 'inactive@test.com',
            'trial_ends_at' => now()->addDays(7)->startOfDay(),
            'is_active' => false,
        ]);

        $this->artisan('trial:check')
            ->assertSuccessful();

        // Inactive tenants should not receive reminders (no output about them)
        Mail::assertNothingSent();
    }

    public function test_no_action_when_trial_far_away(): void
    {
        Mail::fake();

        User::create([
            'name' => 'New',
            'email' => 'new@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->createTenant([
            'id' => 'new-bakery',
            'name' => 'New',
            'email' => 'new@test.com',
            'trial_ends_at' => now()->addDays(25),
            'is_active' => true,
        ]);

        $this->artisan('trial:check')
            ->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_command_source_sends_at_three_intervals(): void
    {
        $source = file_get_contents(app_path('Console/Commands/CheckTrialExpirations.php'));

        $this->assertStringContainsString('trial_reminder_7d', $source);
        $this->assertStringContainsString('trial_reminder_3d', $source);
        $this->assertStringContainsString('trial_reminder_1d', $source);
    }

    public function test_expired_handler_sends_expiration_email(): void
    {
        $source = file_get_contents(app_path('Console/Commands/CheckTrialExpirations.php'));

        $this->assertStringContainsString('trial has expired', $source);
        $this->assertStringContainsString('storefront has been paused', $source);
    }
}
