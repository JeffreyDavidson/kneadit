<?php

namespace Tests\Feature\Central;

use App\Models\AdminAuditLog;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Tests\CentralTestCase;

class CheckChurnAlertsTest extends CentralTestCase
{
    public function test_command_runs_without_errors(): void
    {
        $this->artisan('churn:check')->assertSuccessful();
    }

    public function test_trial_expiring_in_48h_creates_churn_alert(): void
    {
        // Insert tenant directly to avoid HasDatabase trait creating a real DB
        DB::table('tenants')->insert([
            'id' => 'expiring-bakery',
            'name' => 'Expiring Bakery',
            'email' => 'expiring@example.com',
            'plan' => 'starter',
            'trial_ends_at' => Date::now()->addHours(24),
            'data' => '{}',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('churn:check')->assertSuccessful();

        $log = AdminAuditLog::where('action', 'churn_alert')
            ->where('target_id', 'expiring-bakery')
            ->first();

        $this->assertNotNull($log, 'Expected a churn_alert audit log for expiring tenant');
        $this->assertStringContainsString('Trial expiring soon', $log->description);
    }
}
