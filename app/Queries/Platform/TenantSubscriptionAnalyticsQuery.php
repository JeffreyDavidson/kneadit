<?php

namespace App\Queries\Platform;

use App\DataTransferObjects\Settings\SettingValue;
use App\Enums\Platform\SubscriptionTier;
use App\Models\Platform\Tenant;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class TenantSubscriptionAnalyticsQuery
{
    /** @var array{on_trial: int, expired: int, converted: int}|null */
    private ?array $trialConversionMetrics = null;

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
        if ($this->trialConversionMetrics !== null) {
            return $this->trialConversionMetrics;
        }

        $now = now();
        $metrics = Tenant::query()
            ->toBase()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COALESCE(SUM(CASE WHEN trial_ends_at > ? THEN 1 ELSE 0 END), 0) as on_trial', [$now])
            ->selectRaw('COALESCE(SUM(CASE WHEN trial_ends_at IS NOT NULL AND trial_ends_at <= ? THEN 1 ELSE 0 END), 0) as expired', [$now])
            ->first();

        $total = Arr::integer(['value' => $metrics->total ?? 0], 'value', 0);
        $onTrial = Arr::integer(['value' => $metrics->on_trial ?? 0], 'value', 0);
        $expired = Arr::integer(['value' => $metrics->expired ?? 0], 'value', 0);

        return $this->trialConversionMetrics = [
            'on_trial' => $onTrial,
            'expired' => $expired,
            'converted' => $total - $onTrial - $expired,
        ];
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
