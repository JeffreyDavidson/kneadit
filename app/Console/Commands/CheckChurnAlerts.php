<?php

namespace App\Console\Commands;

use App\Models\AdminAuditLog;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class CheckChurnAlerts extends Command
{
    protected $signature = 'churn:check';

    protected $description = 'Check for churn risk indicators and log alerts';

    public function handle(): int
    {
        $tenants = Tenant::all();
        $alertCount = 0;

        foreach ($tenants as $tenant) {
            $name = $tenant->store_name ?? $tenant->name;
            $daysSinceSignup = $tenant->created_at ? (int) Date::parse($tenant->created_at)->diffInDays(now()) : 0;

            // Trial expiring in 48h with low setup
            if ($tenant->trial_ends_at) {
                $trialEnds = Date::parse($tenant->trial_ends_at);
                if ($trialEnds->isFuture() && $trialEnds->diffInHours(now()) <= 48) {
                    $setupScore = $this->getSetupScore($tenant);
                    if ($setupScore < 15) {
                        AdminAuditLog::log(
                            action: 'churn_alert',
                            description: "Trial expiring soon with low setup: {$name} (ends {$trialEnds->diffForHumans()})",
                            targetType: 'tenant',
                            targetId: (string) $tenant->id,
                            metadata: ['type' => 'trial_expiring', 'setup_score' => $setupScore],
                        );
                        $alertCount++;
                    }
                }
            }

            // No login in 7+ days
            $lastLogin = $this->getLastLogin($tenant);
            if ($lastLogin && Date::parse($lastLogin)->diffInDays(now()) >= 7) {
                $days = Date::parse($lastLogin)->diffInDays(now());
                AdminAuditLog::log(
                    action: 'churn_alert',
                    description: "No login in {$days} days: {$name}",
                    targetType: 'tenant',
                    targetId: (string) $tenant->id,
                    metadata: ['type' => 'no_login', 'days' => $days],
                );
                $alertCount++;
            }

            // Zero orders in 30 days (tenants older than 14 days)
            if ($daysSinceSignup > 14) {
                $recentOrders = $this->getRecentOrderCount($tenant, 30);
                if ($recentOrders === 0) {
                    AdminAuditLog::log(
                        action: 'churn_alert',
                        description: "Zero orders in 30 days: {$name}",
                        targetType: 'tenant',
                        targetId: (string) $tenant->id,
                        metadata: ['type' => 'no_orders'],
                    );
                    $alertCount++;
                }
            }
        }

        $this->info("Churn check complete. {$alertCount} alert(s) logged.");

        return self::SUCCESS;
    }

    protected function getLastLogin(Tenant $tenant): ?string
    {
        try {
            tenancy()->initialize($tenant);
            $lastLogin = DB::table('users')->max('updated_at');
            tenancy()->end();

            return $lastLogin;
        } catch (\Throwable) {
            try {
                tenancy()->end();
            } catch (\Throwable) {
            }

            return null;
        }
    }

    protected function getRecentOrderCount(Tenant $tenant, int $days): int
    {
        try {
            tenancy()->initialize($tenant);
            $count = DB::table('orders')
                ->where('created_at', '>=', now()->subDays($days))
                ->count();
            tenancy()->end();

            return $count;
        } catch (\Throwable) {
            try {
                tenancy()->end();
            } catch (\Throwable) {
            }

            return 0;
        }
    }

    protected function getSetupScore(Tenant $tenant): int
    {
        try {
            tenancy()->initialize($tenant);
            $score = 0;
            $score += DB::table('products')->exists() ? 5 : 0;
            $score += DB::table('users')->count() > 1 ? 5 : 0;
            $score += DB::table('orders')->exists() ? 5 : 0;
            // Check for store customization
            $score += DB::table('settings')->exists() ? 5 : 0;
            $score += DB::table('categories')->exists() ? 5 : 0;
            $score += DB::table('media')->exists() ? 5 : 0;
            tenancy()->end();

            return $score;
        } catch (\Throwable) {
            try {
                tenancy()->end();
            } catch (\Throwable) {
            }

            return 0;
        }
    }
}
