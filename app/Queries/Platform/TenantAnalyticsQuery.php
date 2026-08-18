<?php

namespace App\Queries\Platform;

use App\Enums\Platform\SubscriptionTier;
use App\Models\Platform\Tenant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class TenantAnalyticsQuery
{
    /** @return array<int, array<string, mixed>> */
    public static function signupsByMonth(): array
    {
        $startDate = Date::now()->subMonths(11)->startOfMonth();

        $counts = Tenant::query()
            ->where('created_at', '>=', $startDate)
            ->get(['created_at'])
            ->groupBy(fn (Tenant $tenant) => $tenant->created_at?->format('Y-m') ?? '')
            ->map(fn (Collection $group) => $group->count());

        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = Date::now()->subMonths($i);
            $key = $date->format('Y-m');
            $months->push([
                'label' => $date->format('M Y'),
                'count' => (int) ($counts[$key] ?? 0),
            ]);
        }

        return $months->toArray();
    }

    /** @return array<string, mixed> */
    public static function planDistribution(): array
    {
        return Tenant::query()->select('plan', DB::raw('count(*) as count'))
            ->groupBy('plan')
            ->pluck('count', 'plan')
            ->toArray();
    }

    /** @return array<string, int> */
    public static function trialConversion(): array
    {
        $total = Tenant::query()->count();
        $onTrial = Tenant::query()->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>', now())
            ->count();
        $expired = Tenant::query()->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<=', now())
            ->count();
        $converted = $total - $onTrial - $expired;

        return [
            'on_trial' => $onTrial,
            'expired' => $expired,
            'converted' => $converted,
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public static function monthlyGrowth(): array
    {
        $signups = static::signupsByMonth();
        $growth = [];

        for ($i = 1; $i < count($signups); $i++) {
            $prev = $signups[$i - 1]['count'];
            $curr = $signups[$i]['count'];
            $rate = $prev > 0 ? round((($curr - $prev) / $prev) * 100, 1) : 0;
            $growth[] = [
                'label' => $signups[$i]['label'],
                'rate' => $rate,
            ];
        }

        return $growth;
    }

    public static function totalSignups(): int
    {
        return Tenant::query()->count();
    }

    public static function thisMonthSignups(): int
    {
        return Tenant::query()->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
    }

    public static function avgDaysOnTrial(): float
    {
        $tenants = Tenant::query()->whereNotNull('trial_ends_at')
            ->select('trial_ends_at', 'created_at')
            ->get();

        $avgDays = $tenants->avg(fn (Tenant $tenant) => Date::parse($tenant->created_at)
            ->diffInDays(Date::parse($tenant->trial_ends_at)));

        return round($avgDays ?? 0, 1);
    }

    public static function mostPopularPlan(): string
    {
        $plan = Tenant::query()->select('plan', DB::raw('count(*) as count'))
            ->groupBy('plan')
            ->orderByDesc('count')
            ->value('plan');

        if ($plan instanceof SubscriptionTier) {
            return $plan->value;
        }

        return $plan ?? 'N/A';
    }

    /**
     * Headline KPIs with contextual hints.
     *
     * @return array<int, array{label: string, value: string, hint: string, trend: 'up'|'down'|'flat'|'neutral'}>
     */
    public static function kpis(): array
    {
        $total = static::totalSignups();
        $thisMonth = static::thisMonthSignups();
        $conversion = static::trialConversion();
        $active = $conversion['converted'];
        $trialing = $conversion['on_trial'];
        $expired = $conversion['expired'];

        $completedTrials = $active + $expired;
        $conversionRate = $completedTrials > 0 ? round($active / $completedTrials * 100, 1) : 0.0;

        $lastMonthStart = Date::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Date::now()->subMonth()->endOfMonth();
        $lastMonth = Tenant::query()->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();

        return [
            [
                'label' => 'Total Tenants',
                'value' => (string) $total,
                'hint' => $thisMonth > 0 ? "+{$thisMonth} this month" : 'No new signups yet',
                'trend' => $thisMonth > 0 ? 'up' : 'flat',
            ],
            [
                'label' => 'New This Month',
                'value' => (string) $thisMonth,
                'hint' => self::buildMonthDelta($thisMonth, $lastMonth),
                'trend' => match (true) {
                    $thisMonth > $lastMonth => 'up',
                    $thisMonth < $lastMonth => 'down',
                    default => 'flat',
                },
            ],
            [
                'label' => 'Active Subscriptions',
                'value' => (string) $active,
                'hint' => $trialing === 0 && $expired === 0
                    ? 'No trial activity'
                    : "{$trialing} on trial • {$expired} churned",
                'trend' => 'neutral',
            ],
            [
                'label' => 'Trial → Paid',
                'value' => $completedTrials > 0 ? $conversionRate . '%' : '—',
                'hint' => $completedTrials > 0
                    ? "{$active} of {$completedTrials} completed trials converted"
                    : 'No trials completed yet',
                'trend' => match (true) {
                    $completedTrials === 0 => 'neutral',
                    $conversionRate >= 50 => 'up',
                    $conversionRate >= 25 => 'neutral',
                    default => 'down',
                },
            ],
        ];
    }

    /**
     * Breakdown of tenants by lifecycle state.
     *
     * @return array<string, int>
     */
    public static function tenantStatus(): array
    {
        $conversion = static::trialConversion();

        return [
            'Active' => $conversion['converted'],
            'On trial' => $conversion['on_trial'],
            'Trial expired' => $conversion['expired'],
        ];
    }

    private static function buildMonthDelta(int $current, int $previous): string
    {
        if ($previous === 0 && $current === 0) {
            return 'No change vs. last month';
        }

        if ($previous === 0) {
            return 'First month with signups';
        }

        $diffPct = (int) round(($current - $previous) / $previous * 100);
        $arrow = $current >= $previous ? '↑' : '↓';

        return "{$arrow} " . abs($diffPct) . "% vs. last month ({$previous})";
    }
}
