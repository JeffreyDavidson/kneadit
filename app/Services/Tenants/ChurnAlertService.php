<?php

namespace App\Services\Tenants;

use App\Models\Platform\Tenant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

class ChurnAlertService
{
    public function __construct(
        protected TenantHealthService $healthService,
    ) {}

    /** @return Collection<int, array<string, mixed>> */
    public function getAlerts(): Collection
    {
        $tenants = Tenant::all();
        $healthData = $this->healthService->getTenantHealthData()->keyBy('id');
        $alerts = collect();

        foreach ($tenants as $tenant) {
            $daysSinceSignup = $tenant->created_at ? (int) Date::parse($tenant->created_at)->diffInDays(now()) : 0;
            $health = $healthData->get($tenant->id);
            $healthScore = $health ? $health['health_score'] : 0;

            $this->checkTrialExpiring($tenant, $health, $daysSinceSignup, $alerts);
            $this->checkNoLogin($tenant, $daysSinceSignup, $alerts);
            $this->checkNoOrders($tenant, $daysSinceSignup, $alerts);
            $this->checkLowHealth($tenant, $healthScore, $daysSinceSignup, $alerts);
        }

        return $alerts->sortByDesc(fn (array $a) => $a['severity'] === 'critical' ? 1 : 0)->values();
    }

    /**
     * @param array<string, mixed>|null $health
     * @param Collection<int, array<string, mixed>> $alerts
     */
    private function checkTrialExpiring(Tenant $tenant, ?array $health, int $daysSinceSignup, Collection $alerts): void
    {
        if (! $tenant->trial_ends_at) {
            return;
        }

        $trialEnds = Date::parse($tenant->trial_ends_at);
        if (! $trialEnds->isFuture() || $trialEnds->diffInHours(now()) > config('monitoring.churn_trial_alert_hours', 48)) {
            return;
        }

        $setupScore = $health ? $health['setup_score'] : 0;
        if ($setupScore >= config('monitoring.churn_low_setup_threshold', 15)) {
            return;
        }

        $alerts->push([
            'tenant_id' => $tenant->id,
            'name' => $tenant->store_name ?? $tenant->name,
            'type' => 'trial_expiring',
            'type_label' => 'Trial Expiring',
            'description' => "Trial ends {$trialEnds->diffForHumans()} with less than 50% setup complete.",
            'days_since_signup' => $daysSinceSignup,
            'severity' => 'critical',
        ]);
    }

    /** @param Collection<int, array<string, mixed>> $alerts */
    private function checkNoLogin(Tenant $tenant, int $daysSinceSignup, Collection $alerts): void
    {
        $lastLogin = $tenant->last_login_at;
        if (! $lastLogin) {
            return;
        }

        $days = (int) Date::parse($lastLogin)->diffInDays(now());
        if ($days < config('monitoring.churn_no_login_days', 7)) {
            return;
        }

        $alerts->push([
            'tenant_id' => $tenant->id,
            'name' => $tenant->store_name ?? $tenant->name,
            'type' => 'no_login',
            'type_label' => 'No Recent Login',
            'description' => "No login activity in {$days} days.",
            'days_since_signup' => $daysSinceSignup,
            'severity' => 'warning',
        ]);
    }

    /** @param Collection<int, array<string, mixed>> $alerts */
    private function checkNoOrders(Tenant $tenant, int $daysSinceSignup, Collection $alerts): void
    {
        if ($daysSinceSignup <= config('monitoring.churn_min_tenant_age_days', 14)) {
            return;
        }

        $recentOrders = $this->healthService->getRecentOrderCount($tenant, config('monitoring.churn_no_orders_days', 30));
        if ($recentOrders > 0) {
            return;
        }

        $alerts->push([
            'tenant_id' => $tenant->id,
            'name' => $tenant->store_name ?? $tenant->name,
            'type' => 'no_orders',
            'type_label' => 'No Orders',
            'description' => 'Zero orders in the last ' . config('monitoring.churn_no_orders_days', 30) . ' days.',
            'days_since_signup' => $daysSinceSignup,
            'severity' => 'warning',
        ]);
    }

    /** @param Collection<int, array<string, mixed>> $alerts */
    private function checkLowHealth(Tenant $tenant, int $healthScore, int $daysSinceSignup, Collection $alerts): void
    {
        if ($healthScore >= config('monitoring.churn_low_health_threshold', 40)) {
            return;
        }

        $alerts->push([
            'tenant_id' => $tenant->id,
            'name' => $tenant->store_name ?? $tenant->name,
            'type' => 'low_health',
            'type_label' => 'Critical Health',
            'description' => "Health score is {$healthScore}/100.",
            'days_since_signup' => $daysSinceSignup,
            'severity' => 'critical',
        ]);
    }
}
