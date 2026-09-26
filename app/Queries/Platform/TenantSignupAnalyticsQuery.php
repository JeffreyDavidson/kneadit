<?php

namespace App\Queries\Platform;

use App\Models\Platform\Tenant;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;

class TenantSignupAnalyticsQuery
{
    /** @var array<int, array{label: string, count: int}>|null */
    private ?array $monthlySignups = null;

    private ?int $totalSignups = null;

    private ?int $currentMonthSignups = null;

    /** @var array{total: int, this_month: int, last_month: int}|null */
    private ?array $summaryCounts = null;

    /** @return array<int, array{label: string, count: int}> */
    public function byMonth(): array
    {
        if ($this->monthlySignups !== null) {
            return $this->monthlySignups;
        }

        $now = Date::now();
        $startDate = $now->copy()->subMonths(11)->startOfMonth();

        $counts = Tenant::query()
            ->where('created_at', '>=', $startDate)
            ->selectRaw('SUBSTR(created_at, 1, 7) as month, COUNT(*) as aggregate')
            ->groupBy('month')
            ->pluck('aggregate', 'month')
            ->all();

        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $key = $date->format('Y-m');
            $months[] = [
                'label' => $date->format('M Y'),
                'count' => Arr::integer(['value' => $counts[$key] ?? 0], 'value', 0),
            ];
        }

        return $this->monthlySignups = $months;
    }

    /** @return array<int, array{label: string, rate: float|int}> */
    public function monthlyGrowth(): array
    {
        $signups = $this->byMonth();
        $growth = [];
        $counter = count($signups);

        for ($i = 1; $i < $counter; $i++) {
            $previous = $signups[$i - 1]['count'];
            $current = $signups[$i]['count'];
            $growth[] = [
                'label' => $signups[$i]['label'],
                'rate' => $previous > 0 ? round((($current - $previous) / $previous) * 100, 1) : 0,
            ];
        }

        return $growth;
    }

    public function total(): int
    {
        return $this->totalSignups ??= Tenant::query()->count();
    }

    public function thisMonth(): int
    {
        return $this->currentMonthSignups ??= Tenant::query()->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
    }

    /** @return array{total: int, this_month: int, last_month: int} */
    public function summaryCounts(): array
    {
        if ($this->summaryCounts !== null) {
            return $this->summaryCounts;
        }

        $now = Date::now();
        $monthStart = $now->copy()->startOfMonth();
        $nextMonthStart = $monthStart->copy()->addMonth();
        $previousMonth = $now->copy()->subMonth();
        $query = Tenant::query()
            ->toBase()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COUNT(CASE WHEN created_at >= ? AND created_at < ? THEN 1 END) as this_month')
            ->addBinding([$monthStart, $nextMonthStart], 'select')
            ->selectRaw('COUNT(CASE WHEN created_at BETWEEN ? AND ? THEN 1 END) as last_month')
            // Keep period boundaries as bindings, never interpolate them into the SQL expression.
            ->addBinding([
                $previousMonth->copy()->startOfMonth(),
                $previousMonth->copy()->endOfMonth(),
            ], 'select');
        $counts = $query->first();

        return $this->summaryCounts = [
            'total' => Arr::integer(['value' => $counts->total ?? 0], 'value', 0),
            'this_month' => Arr::integer(['value' => $counts->this_month ?? 0], 'value', 0),
            'last_month' => Arr::integer(['value' => $counts->last_month ?? 0], 'value', 0),
        ];
    }
}
