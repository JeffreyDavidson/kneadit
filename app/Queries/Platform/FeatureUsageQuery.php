<?php

namespace App\Queries\Platform;

use App\Models\Platform\FeatureUsageLog;
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
        return FeatureUsageLog::query()->select('feature')
            ->selectRaw('SUM(usage_count) as total')
            ->groupBy('feature')
            ->orderByDesc('total')
            ->value('feature');
    }

    public static function leastUsedFeature(): ?string
    {
        return FeatureUsageLog::query()->select('feature')
            ->selectRaw('SUM(usage_count) as total')
            ->groupBy('feature')
            ->orderBy('total')
            ->value('feature');
    }

    public static function totalInteractionsThisMonth(): int
    {
        return (int) FeatureUsageLog::query()->whereMonth('date', Date::now()->month)
            ->whereYear('date', Date::now()->year)
            ->sum('usage_count');
    }

    /** @return Collection<int, mixed> */
    public static function featureUsageBars(): Collection
    {
        $data = FeatureUsageLog::query()->select('feature')
            ->selectRaw('SUM(usage_count) as total')
            ->groupBy('feature')
            ->orderByDesc('total')
            ->get();

        $max = $data->max('total') ?: 1;

        return $data->map(fn (FeatureUsageLog $row) => [
            'feature' => $row->feature,
            'total' => $row->total,
            'percent' => round(($row->total / $max) * 100),
        ]);
    }

    /** @return array<string, mixed> */
    public static function heatmapData(): array
    {
        $days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $days->push(Date::today()->subDays($i));
        }

        $features = FeatureUsageLog::query()->distinct()->pluck('feature')->sort()->values();

        $logs = FeatureUsageLog::query()->whereBetween('date', [$days->first()->toDateString(), $days->last()->toDateString()])
            ->get()
            ->groupBy(fn (FeatureUsageLog $log) => $log->feature . '|' . $log->date->toDateString());

        $maxCount = $logs->max(fn (Collection $group) => $group->sum('usage_count')) ?: 1;

        $rows = [];
        foreach ($features as $feature) {
            $cells = [];
            foreach ($days as $day) {
                $key = $feature . '|' . $day->toDateString();
                $count = isset($logs[$key]) ? $logs[$key]->sum('usage_count') : 0;
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

    /** @return Collection<int, mixed> */
    public static function featureTenantBreakdown(string $feature): Collection
    {
        return FeatureUsageLog::query()->select('tenant_id')
            ->selectRaw('SUM(usage_count) as total')
            ->where('feature', $feature)
            ->groupBy('tenant_id')
            ->orderByDesc('total')
            ->limit(20)
            ->get();
    }
}
