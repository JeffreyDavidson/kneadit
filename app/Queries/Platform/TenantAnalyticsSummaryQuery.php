<?php

namespace App\Queries\Platform;

use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\Date;

class TenantAnalyticsSummaryQuery
{
    public function __construct(
        private readonly TenantSignupAnalyticsQuery $signups,
        private readonly TenantSubscriptionAnalyticsQuery $subscriptions,
    ) {}

    /** @return array<int, array{label: string, value: string, hint: string, trend: 'up'|'down'|'flat'|'neutral'}> */
    public function kpis(): array
    {
        $total = $this->signups->total();
        $thisMonth = $this->signups->thisMonth();
        $conversion = $this->subscriptions->trialConversion();
        $active = $conversion['converted'];
        $completedTrials = $active + $conversion['expired'];
        $conversionRate = $completedTrials > 0 ? round($active / $completedTrials * 100, 1) : 0.0;
        $lastMonth = Tenant::query()->whereBetween('created_at', [Date::now()->subMonth()->startOfMonth(), Date::now()->subMonth()->endOfMonth()])->count();

        return [
            ['label' => 'Total Tenants', 'value' => (string) $total, 'hint' => $thisMonth > 0 ? "+{$thisMonth} this month" : 'No new signups yet', 'trend' => $thisMonth > 0 ? 'up' : 'flat'],
            ['label' => 'New This Month', 'value' => (string) $thisMonth, 'hint' => $this->monthDelta($thisMonth, $lastMonth), 'trend' => match (true) {
                $thisMonth > $lastMonth => 'up', $thisMonth < $lastMonth => 'down', default => 'flat',
            }],
            ['label' => 'Active Subscriptions', 'value' => (string) $active, 'hint' => $conversion['on_trial'] === 0 && $conversion['expired'] === 0 ? 'No trial activity' : "{$conversion['on_trial']} on trial • {$conversion['expired']} churned", 'trend' => 'neutral'],
            ['label' => 'Trial → Paid', 'value' => $completedTrials > 0 ? $conversionRate.'%' : '—', 'hint' => $completedTrials > 0 ? "{$active} of {$completedTrials} completed trials converted" : 'No trials completed yet', 'trend' => match (true) {
                $completedTrials === 0 => 'neutral', $conversionRate >= 50 => 'up', $conversionRate >= 25 => 'neutral', default => 'down',
            }],
        ];
    }

    /** @return array<string, int> */
    public function tenantStatus(): array
    {
        $conversion = $this->subscriptions->trialConversion();

        return ['Active' => $conversion['converted'], 'On trial' => $conversion['on_trial'], 'Trial expired' => $conversion['expired']];
    }

    private function monthDelta(int $current, int $previous): string
    {
        if ($previous === 0 && $current === 0) {
            return 'No change vs. last month';
        }
        if ($previous === 0) {
            return 'First month with signups';
        }
        $diff = (int) round(($current - $previous) / $previous * 100);

        return ($current >= $previous ? '↑' : '↓').' '.abs($diff)."% vs. last month ({$previous})";
    }
}
