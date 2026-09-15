<?php

namespace App\Queries\Platform;

use App\DataTransferObjects\Settings\SettingValue;
use App\Enums\Platform\SubscriptionTier;
use App\Models\Platform\Tenant;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;

class TenantSubscriptionAnalyticsQuery
{
    /** @var array<int, array{plan: mixed, count: mixed}>|null */
    private ?array $planCounts = null;

    /** @var array{on_trial: int, expired: int, converted: int}|null */
    private ?array $trialConversionMetrics = null;

    /** @return array<string, mixed> */
    public function planDistribution(): array
    {
        $plans = [];

        foreach ($this->planCounts() as $row) {
            $plan = $row['plan'] instanceof SubscriptionTier ? $row['plan']->value : $row['plan'];

            if (! is_string($plan)) {
                continue;
            }

            $plans[$plan] = Arr::integer(['value' => $row['count']], 'value', 0);
        }

        return SettingValue::map($plans);
    }

    /** @return array{on_trial: int, expired: int, converted: int} */
    public function trialConversion(): array
    {
        if ($this->trialConversionMetrics !== null) {
            return $this->trialConversionMetrics;
        }

        $metrics = Tenant::query()
            ->toBase()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COALESCE(SUM(CASE WHEN trial_ends_at > CURRENT_TIMESTAMP THEN 1 ELSE 0 END), 0) as on_trial')
            ->selectRaw('COALESCE(SUM(CASE WHEN trial_ends_at IS NOT NULL AND trial_ends_at <= CURRENT_TIMESTAMP THEN 1 ELSE 0 END), 0) as expired')
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
        $plan = $this->planCounts()[0]['plan'] ?? null;

        return $plan instanceof SubscriptionTier ? $plan->value : (is_string($plan) ? $plan : 'N/A');
    }

    /** @return array<int, array{plan: mixed, count: mixed}> */
    private function planCounts(): array
    {
        return $this->planCounts ??= Tenant::query()
            ->select('plan')
            ->selectRaw('count(*) as count')
            ->groupBy('plan')
            ->orderByDesc('count')
            ->toBase()
            ->get()
            ->map(fn (object $row): array => [
                'plan' => $row->plan,
                'count' => $row->count,
            ])
            ->all();
    }
}
