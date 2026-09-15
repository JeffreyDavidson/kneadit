<?php

namespace App\Queries\Platform;

use App\Models\Platform\Tenant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

class TenantSignupAnalyticsQuery
{
    /** @var array<int, array{label: string, count: int}>|null */
    private ?array $monthlySignups = null;

    private ?int $totalSignups = null;

    private ?int $currentMonthSignups = null;

    /** @return array<int, array{label: string, count: int}> */
    public function byMonth(): array
    {
        if ($this->monthlySignups !== null) {
            return $this->monthlySignups;
        }

        $startDate = Date::now()->subMonths(11)->startOfMonth();

        $counts = Tenant::query()
            ->where('created_at', '>=', $startDate)
            ->get(['created_at'])
            ->groupBy(fn (Tenant $tenant) => $tenant->created_at?->format('Y-m') ?? '')
            ->map(fn (Collection $group) => $group->count());

        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Date::now()->subMonths($i);
            $key = $date->format('Y-m');
            $months[] = [
                'label' => $date->format('M Y'),
                'count' => (int) ($counts[$key] ?? 0),
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
}
