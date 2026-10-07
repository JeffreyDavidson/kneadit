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

    /** @var array{on_trial: int, expired: int, converted: int, paying: int}|null */
    private ?array $trialConversionMetrics = null;

    public function __construct(private readonly PlatformRevenueMetricsQuery $revenue) {}

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

    /**
     * Where bakeries stand after their trial. Whether an owner converted or
     * pays comes from their Cashier subscription (the revenue metrics), never
     * from the bakery's own columns. Comped and demo bakeries are not in the
     * funnel.
     *
     * @return array{on_trial: int, expired: int, converted: int, paying: int}
     */
    public function trialConversion(): array
    {
        if ($this->trialConversionMetrics !== null) {
            return $this->trialConversionMetrics;
        }

        $revenue = $this->revenue->get();

        return $this->trialConversionMetrics = [
            'on_trial' => Tenant::query()
                ->where('free_forever', false)
                ->where('is_demo', false)
                ->where('trial_ends_at', '>', now())
                ->count(),
            'expired' => $revenue->trialedCount - $revenue->convertedCount,
            'converted' => $revenue->convertedCount,
            'paying' => $revenue->payingCount,
        ];
    }

    public function averageTrialDays(): float
    {
        $totalDays = 0.0;
        $trialCount = 0;

        foreach (Tenant::query()
            ->whereNotNull('trial_ends_at')
            ->select('trial_ends_at', 'created_at')
            ->cursor() as $tenant) {
            $totalDays += (float) Date::parse($tenant->created_at)->diffInDays(Date::parse($tenant->trial_ends_at));
            $trialCount++;
        }

        return round($trialCount > 0 ? $totalDays / $trialCount : 0.0, 1);
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
