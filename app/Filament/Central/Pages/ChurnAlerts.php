<?php

namespace App\Filament\Central\Pages;

use App\Models\Tenant;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class ChurnAlerts extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static string|UnitEnum|null $navigationGroup = 'Insights';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Churn Alerts';

    protected string $view = 'filament.central.pages.churn-alerts';

    public function getAlerts(): Collection
    {
        $tenants = Tenant::all();
        $healthPage = new TenantHealth();
        $healthData = $healthPage->getTenantHealthData()->keyBy('id');
        $alerts = collect();

        foreach ($tenants as $tenant) {
            $daysSinceSignup = $tenant->created_at ? (int) Carbon::parse($tenant->created_at)->diffInDays(now()) : 0;
            $health = $healthData->get($tenant->id);
            $healthScore = $health ? $health['health_score'] : 0;

            // Trial ending in 48h with setup < 50%
            if ($tenant->trial_ends_at) {
                $trialEnds = Carbon::parse($tenant->trial_ends_at);
                if ($trialEnds->isFuture() && $trialEnds->diffInHours(now()) <= 48) {
                    $setupScore = $health ? $health['setup_score'] : 0;
                    if ($setupScore < 15) { // 15/30 = 50%
                        $alerts->push([
                            'tenant_id' => $tenant->id,
                            'name' => $tenant->store_name ?? $tenant->name,
                            'type' => 'trial_expiring',
                            'type_label' => 'Trial Expiring',
                            'description' => 'Trial ends in ' . $trialEnds->diffForHumans() . ' with less than 50% setup complete.',
                            'days_since_signup' => $daysSinceSignup,
                            'severity' => 'critical',
                        ]);
                    }
                }
            }

            // No login in 7+ days
            $lastLogin = $this->getLastLogin($tenant);
            if ($lastLogin && Carbon::parse($lastLogin)->diffInDays(now()) >= 7) {
                $alerts->push([
                    'tenant_id' => $tenant->id,
                    'name' => $tenant->store_name ?? $tenant->name,
                    'type' => 'no_login',
                    'type_label' => 'No Recent Login',
                    'description' => 'No login activity in ' . Carbon::parse($lastLogin)->diffInDays(now()) . ' days.',
                    'days_since_signup' => $daysSinceSignup,
                    'severity' => 'warning',
                ]);
            }

            // Zero orders in 30 days (tenants older than 14 days)
            if ($daysSinceSignup > 14) {
                $recentOrders = $this->getRecentOrderCount($tenant, 30);
                if ($recentOrders === 0) {
                    $alerts->push([
                        'tenant_id' => $tenant->id,
                        'name' => $tenant->store_name ?? $tenant->name,
                        'type' => 'no_orders',
                        'type_label' => 'No Orders',
                        'description' => 'Zero orders in the last 30 days.',
                        'days_since_signup' => $daysSinceSignup,
                        'severity' => 'warning',
                    ]);
                }
            }

            // Health score < 40
            if ($healthScore < 40) {
                $alerts->push([
                    'tenant_id' => $tenant->id,
                    'name' => $tenant->store_name ?? $tenant->name,
                    'type' => 'low_health',
                    'type_label' => 'Critical Health',
                    'description' => 'Health score is ' . $healthScore . '/100.',
                    'days_since_signup' => $daysSinceSignup,
                    'severity' => 'critical',
                ]);
            }
        }

        return $alerts->sortByDesc(fn ($a) => $a['severity'] === 'critical' ? 1 : 0)->values();
    }

    protected function getLastLogin(Tenant $tenant): ?string
    {
        try {
            tenancy()->initialize($tenant);
            $lastLogin = DB::table('users')->max('updated_at');
            tenancy()->end();
            return $lastLogin;
        } catch (\Throwable $e) {
            try { tenancy()->end(); } catch (\Throwable) {}
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
        } catch (\Throwable $e) {
            try { tenancy()->end(); } catch (\Throwable) {}
            return 0;
        }
    }

    public function getViewTenantUrl(string $tenantId): string
    {
        return \App\Filament\Central\Resources\TenantResource::getUrl('view', ['record' => $tenantId]);
    }
}
