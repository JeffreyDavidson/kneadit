<?php

namespace App\Queries\Platform;

use App\Models\Platform\FeatureUsageLog;
use App\Models\Platform\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

class FeatureUsageQuery
{
    public static function hasData(): bool
    {
        return FeatureUsageLog::query()->exists();
    }

    public static function mostUsedFeature(): ?string
    {
        $feature = FeatureUsageLog::query()->select('feature')
            ->selectRaw('SUM(usage_count) as total')
            ->groupBy('feature')
            ->orderByDesc('total')
            ->value('feature');

        return is_string($feature) ? $feature : null;
    }

    public static function leastUsedFeature(): ?string
    {
        $feature = FeatureUsageLog::query()->select('feature')
            ->selectRaw('SUM(usage_count) as total')
            ->groupBy('feature')
            ->orderBy('total')
            ->value('feature');

        return is_string($feature) ? $feature : null;
    }

    public static function totalInteractionsThisMonth(): int
    {
        return (int) FeatureUsageLog::query()->whereMonth('date', Date::now()->month)
            ->whereYear('date', Date::now()->year)
            ->sum('usage_count');
    }

    public static function totalInteractionsAllTime(): int
    {
        return (int) FeatureUsageLog::query()->sum('usage_count');
    }

    public static function featureTotalCount(?string $feature): int
    {
        if (! $feature) {
            return 0;
        }

        return (int) FeatureUsageLog::query()->where('feature', $feature)->sum('usage_count');
    }

    /** @return Collection<int, array{feature: string, total: int, percent: float}> */
    public static function featureUsageBars(): Collection
    {
        $data = FeatureUsageLog::query()->select('feature')
            ->selectRaw('SUM(usage_count) as total')
            ->groupBy('feature')
            ->orderByDesc('total')
            ->get();

        $maxValue = $data->max('total');
        $max = is_numeric($maxValue) ? (int) $maxValue : 1;

        return $data->map(function (FeatureUsageLog $row) use ($max): array {
            $totalValue = $row->getAttribute('total');
            $total = is_numeric($totalValue) ? (int) $totalValue : 0;

            return [
                'feature' => $row->feature,
                'total' => $total,
                'percent' => round(($total / max($max, 1)) * 100),
            ];
        });
    }

    /** @return array<string, mixed> */
    public static function heatmapData(): array
    {
        $days = collect(range(6, 0))->map(fn (int $daysAgo): Carbon => Date::today()->subDays($daysAgo));

        $features = FeatureUsageLog::query()
            ->distinct()
            ->pluck('feature')
            ->map(fn (mixed $feature): string => is_string($feature) ? $feature : '')
            ->filter()
            ->sort()
            ->values();

        $logs = FeatureUsageLog::query()->whereBetween('date', [
            Date::today()->subDays(6)->toDateString(),
            Date::today()->toDateString(),
        ])
            ->get()
            ->groupBy(fn (FeatureUsageLog $log) => $log->feature . '|' . $log->date->toDateString());

        $maxValue = $logs->max(
            fn (Collection $group): mixed => $group->sum('usage_count'),
        );
        $maxCount = is_numeric($maxValue) ? (int) $maxValue : 1;

        $rows = [];
        foreach ($features as $feature) {
            $cells = [];
            foreach ($days as $day) {
                $key = $feature . '|' . $day->toDateString();
                $count = isset($logs[$key])
                    ? (is_numeric($countValue = $logs[$key]->sum('usage_count')) ? (int) $countValue : 0)
                    : 0;
                $intensity = $maxCount > 0 ? $count / $maxCount : 0;
                $cells[] = [
                    'date' => $day->format('M d'),
                    'count' => $count,
                    'intensity' => $intensity,
                ];
            }
            $rows[] = [
                'feature' => $feature,
                'cells' => $cells,
            ];
        }

        return [
            'days' => $days->map(fn (Carbon $d) => $d->format('M d'))->toArray(),
            'rows' => $rows,
        ];
    }

    /** @return Collection<int, array{tenant_id: string, name: string, total: int}> */
    public static function featureTenantBreakdown(string $feature): Collection
    {
        $rows = FeatureUsageLog::query()->select('tenant_id')
            ->selectRaw('SUM(usage_count) as total')
            ->where('feature', $feature)
            ->groupBy('tenant_id')
            ->orderByDesc('total')
            ->limit(20)
            ->get();

        $tenantNames = Tenant::query()
            ->whereIn('id', $rows->pluck('tenant_id')->all())
            ->pluck('store_name', 'id');

        $fallbackNames = Tenant::query()
            ->whereIn('id', $rows->pluck('tenant_id')->all())
            ->pluck('name', 'id');

        return $rows->map(function (FeatureUsageLog $row) use ($tenantNames, $fallbackNames) {
            return [
                'tenant_id' => $row->tenant_id,
                'name' => is_string($tenantNames[$row->tenant_id] ?? null)
                    ? $tenantNames[$row->tenant_id]
                    : (is_string($fallbackNames[$row->tenant_id] ?? null) ? $fallbackNames[$row->tenant_id] : $row->tenant_id),
                'total' => (int) $row->total,
            ];
        });
    }
}
