<?php

namespace App\Services\Tenants;

use App\DataTransferObjects\Tenants\ChurnAlert;
use App\DataTransferObjects\Tenants\TenantHealthData;
use App\Enums\Tenants\ChurnAlertType;
use App\Enums\Tenants\ChurnSeverity;
use App\Models\Platform\Tenant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;

class ChurnAlertService
{
    public function __construct(
        protected TenantHealthService $healthService,
    ) {}

    /** @return Collection<int, ChurnAlert> */
    public function getAlerts(): Collection
    {
        $tenants = Tenant::all();
        $healthData = $this->healthService->getTenantHealthData()->keyBy('tenantId');
        $alerts = [];

        foreach ($tenants as $tenant) {
            $daysSinceSignup = $tenant->created_at ? (int) Date::parse($tenant->created_at)->diffInDays(now()) : 0;
            $health = $healthData->get($tenant->id) ?? TenantHealthData::unavailable(
                tenantId: $tenant->id,
                name: $tenant->store_name ?? $tenant->name,
                owner: $tenant->name,
                email: $tenant->email,
                plan: $tenant->plan->value ?? 'trial',
            );
            $healthScore = $health->healthScore;

            $this->checkTrialExpiring($tenant, $health, $daysSinceSignup, $alerts);
            $this->checkNoLogin($tenant, $daysSinceSignup, $alerts);
            $this->checkNoOrders($tenant, $daysSinceSignup, $alerts);
            $this->checkLowHealth($tenant, $healthScore, $daysSinceSignup, $alerts);
        }

        return collect($alerts)->sortByDesc(fn (ChurnAlert $alert): int => $alert->severity->priority())->values();
    }

    /**
     * @param array<int, ChurnAlert> $alerts
     */
    private function checkTrialExpiring(Tenant $tenant, TenantHealthData $health, int $daysSinceSignup, array &$alerts): void
    {
        if (! $tenant->trial_ends_at) {
            return;
        }

        $trialEnds = Date::parse($tenant->trial_ends_at);
        if (! $trialEnds->isFuture() || abs($trialEnds->diffInHours(now())) > $this->configInt('monitoring.churn_trial_alert_hours', 48)) {
            return;
        }

        $setupScore = $health->setupScore;
        if ($setupScore >= $this->configInt('monitoring.churn_low_setup_threshold', 15)) {
            return;
        }

        $alerts[] = new ChurnAlert($tenant->id, $tenant->store_name ?? $tenant->name, ChurnAlertType::TrialExpiring, "Trial ends {$trialEnds->diffForHumans()} with less than 50% setup complete.", $daysSinceSignup, ChurnSeverity::Critical);
    }

    /** @param array<int, ChurnAlert> $alerts */
    private function checkNoLogin(Tenant $tenant, int $daysSinceSignup, array &$alerts): void
    {
        $lastLogin = $tenant->last_login_at;
        if (! $lastLogin) {
            return;
        }

        $days = (int) Date::parse($lastLogin)->diffInDays(now());
        if ($days < $this->configInt('monitoring.churn_no_login_days', 7)) {
            return;
        }

        $alerts[] = new ChurnAlert($tenant->id, $tenant->store_name ?? $tenant->name, ChurnAlertType::NoLogin, "No login activity in {$days} days.", $daysSinceSignup, ChurnSeverity::Warning);
    }

    /** @param array<int, ChurnAlert> $alerts */
    private function checkNoOrders(Tenant $tenant, int $daysSinceSignup, array &$alerts): void
    {
        if ($daysSinceSignup <= $this->configInt('monitoring.churn_min_tenant_age_days', 14)) {
            return;
        }

        $days = $this->configInt('monitoring.churn_no_orders_days', 30);
        $recentOrders = $this->healthService->getRecentOrderCount($tenant, $days);
        if ($recentOrders > 0) {
            return;
        }

        $alerts[] = new ChurnAlert($tenant->id, $tenant->store_name ?? $tenant->name, ChurnAlertType::NoOrders, "Zero orders in the last {$days} days.", $daysSinceSignup, ChurnSeverity::Warning);
    }

    /** @param array<int, ChurnAlert> $alerts */
    private function checkLowHealth(Tenant $tenant, int $healthScore, int $daysSinceSignup, array &$alerts): void
    {
        if ($healthScore >= $this->configInt('monitoring.churn_low_health_threshold', 40)) {
            return;
        }

        $alerts[] = new ChurnAlert($tenant->id, $tenant->store_name ?? $tenant->name, ChurnAlertType::LowHealth, "Health score is {$healthScore}/100.", $daysSinceSignup, ChurnSeverity::Critical);
    }

    private function configInt(string $key, int $default): int
    {
        return Config::integer($key, $default);
    }
}
