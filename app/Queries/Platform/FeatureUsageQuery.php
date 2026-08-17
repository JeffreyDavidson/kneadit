<?php

namespace App\Queries\Platform;

use App\Models\Platform\FeatureUsageLog;
use App\Models\Platform\Tenant;
use App\Support\DatabaseValue;
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
        return DatabaseValue::nullableString(FeatureUsageLog::query()->select('feature')
            ->selectRaw('SUM(usage_count) as total')
            ->groupBy('feature')
            ->orderByDesc('total')
            ->value('feature'));
    }

    public static function leastUsedFeature(): ?string
    {
        return DatabaseValue::nullableString(FeatureUsageLog::query()->select('feature')
            ->selectRaw('SUM(usage_count) as total')
            ->groupBy('feature')
            ->orderBy('total')
            ->value('feature'));
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

        $max = DatabaseValue::int($data->max('total'), 1);

        return $data->map(fn (FeatureUsageLog $row) => [
            'feature' => $row->feature,
            'total' => DatabaseValue::int($row->total),
            'percent' => round((DatabaseValue::int($row->total) / max($max, 1)) * 100),
        ]);
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

        $maxCount = DatabaseValue::int($logs->max(
            fn (Collection $group): int => DatabaseValue::int($group->sum('usage_count')),
        ), 1);

        $rows = [];
        foreach ($features as $feature) {
            $cells = [];
            foreach ($days as $day) {
                $key = $feature . '|' . $day->toDateString();
                $count = isset($logs[$key]) ? DatabaseValue::int($logs[$key]->sum('usage_count')) : 0;
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
                'name' => DatabaseValue::nullableString($tenantNames[$row->tenant_id] ?? null)
                    ?? DatabaseValue::nullableString($fallbackNames[$row->tenant_id] ?? null)
                    ?? $row->tenant_id,
                'total' => DatabaseValue::int($row->total),
            ];
        });
    }
}
