<?php

namespace App\Console\Commands\Platform;

use App\Actions\Platform\LogAuditEntry;
use App\Models\Platform\Tenant;
use App\Services\Tenants\TenancyManager;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

#[Signature('churn:check')]
#[Description('Check for churn risk indicators and log alerts')]
class CheckChurnAlertsCommand extends Command
{
    public function handle(TenancyManager $tenancyManager): int
    {
        $tenants = Tenant::query()->cursor();
        $alertCount = 0;

        foreach ($tenants as $tenant) {
            $name = $tenant->store_name ?? $tenant->name;
            $daysSinceSignup = $tenant->created_at ? (int) Date::parse($tenant->created_at)->diffInDays(now()) : 0;

            // Trial expiring in 48h with low setup (setup score requires tenant DB)
            if ($tenant->trial_ends_at) {
                $trialEnds = Date::parse($tenant->trial_ends_at);
                if ($trialEnds->isFuture() && $trialEnds->diffInHours(now()) <= config('monitoring.churn_trial_alert_hours')) {
                    try {
                        $setupScore = $tenancyManager->withinTenant($tenant, fn () => $this->getSetupScore());
                    } catch (\Throwable $e) {
                        Log::warning('Churn check: setup score query failed', ['tenant' => $tenant->id, 'error' => $e->getMessage()]);
                        $setupScore = 0;
                    }
                    if ($setupScore < config('monitoring.churn_low_setup_threshold')) {
                        resolve(LogAuditEntry::class)(
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

            // No login in 7+ days (requires tenant DB)
            try {
                $lastLogin = $tenancyManager->withinTenant($tenant, fn () => DB::table('users')->max('updated_at'));
            } catch (\Throwable $e) {
                Log::warning('Churn check: last login query failed', ['tenant' => $tenant->id, 'error' => $e->getMessage()]);
                $lastLogin = null;
            }
            if ($lastLogin && Date::parse($lastLogin)->diffInDays(now()) >= config('monitoring.churn_no_login_days')) {
                $days = (int) Date::parse($lastLogin)->diffInDays(now());
                resolve(LogAuditEntry::class)(
                    action: 'churn_alert',
                    description: "No login in {$days} days: {$name}",
                    targetType: 'tenant',
                    targetId: (string) $tenant->id,
                    metadata: ['type' => 'no_login', 'days' => $days],
                );
                $alertCount++;
            }

            // Zero orders in configured period (tenants older than 14 days, requires tenant DB)
            if ($daysSinceSignup > config('monitoring.churn_min_tenant_age_days')) {
                try {
                    $recentOrders = $tenancyManager->withinTenant($tenant, fn () => DB::table('orders')
                        ->where('created_at', '>=', now()->subDays(config('monitoring.churn_no_orders_days')))
                        ->count());
                } catch (\Throwable $e) {
                    Log::warning('Churn check: recent orders query failed', ['tenant' => $tenant->id, 'error' => $e->getMessage()]);
                    $recentOrders = 0;
                }
                if ($recentOrders === 0) {
                    resolve(LogAuditEntry::class)(
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

    protected function getSetupScore(): int
    {
        $score = 0;
        $score += DB::table('products')->exists() ? 5 : 0;
        $score += DB::table('users')->count() > 1 ? 5 : 0;
        $score += DB::table('orders')->exists() ? 5 : 0;
        $score += DB::table('settings')->exists() ? 5 : 0;
        $score += DB::table('categories')->exists() ? 5 : 0;
        $score += DB::table('media')->exists() ? 5 : 0;

        return $score;
    }
}
