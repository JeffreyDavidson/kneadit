<?php

namespace App\Queries\Platform;

use App\DataTransferObjects\Settings\SettingValue;
use App\Enums\Platform\SubscriptionTier;
use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class TenantSubscriptionAnalyticsQuery
{
    /** @return array<string, mixed> */
    public function planDistribution(): array
    {
        $plans = Tenant::query()->select('plan', DB::raw('count(*) as count'))
            ->groupBy('plan')
            ->pluck('count', 'plan')
            ->all();

        return SettingValue::map($plans);
    }

    /** @return array{on_trial: int, expired: int, converted: int} */
    public function trialConversion(): array
    {
        $total = Tenant::query()->count();
        $onTrial = Tenant::query()->whereNotNull('trial_ends_at')->where('trial_ends_at', '>', now())->count();
        $expired = Tenant::query()->whereNotNull('trial_ends_at')->where('trial_ends_at', '<=', now())->count();

        return ['on_trial' => $onTrial, 'expired' => $expired, 'converted' => $total - $onTrial - $expired];
    }

    public function averageTrialDays(): float
    {
        $tenants = Tenant::query()->whereNotNull('trial_ends_at')->select('trial_ends_at', 'created_at')->get();
        $average = $tenants->avg(fn (Tenant $tenant) => Date::parse($tenant->created_at)->diffInDays(Date::parse($tenant->trial_ends_at)));

        return round($average ?? 0, 1);
    }

    public function mostPopularPlan(): string
    {
        $plan = Tenant::query()->select('plan', DB::raw('count(*) as count'))
            ->groupBy('plan')->orderByDesc('count')->value('plan');

        return $plan instanceof SubscriptionTier ? $plan->value : (is_string($plan) ? $plan : 'N/A');
    }
}
