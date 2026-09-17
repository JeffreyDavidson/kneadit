<?php

namespace App\Queries\Platform;

use App\DataTransferObjects\Platform\FeatureTenantUsage;
use App\DataTransferObjects\Platform\FeatureUsageBar;
use App\DataTransferObjects\Platform\FeatureUsageHeatmap;
use App\Models\Platform\FeatureUsageLog;
use App\Models\Platform\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

class FeatureUsageQuery
{
    public function hasData(): bool
    {
        return FeatureUsageLog::query()->exists();
    }

    public function mostUsedFeature(): ?string
    {
        $feature = FeatureUsageLog::query()->select('feature')
            ->selectRaw('SUM(usage_count) as total')
            ->groupBy('feature')
            ->orderByDesc('total')
            ->value('feature');

        return is_string($feature) ? $feature : null;
    }

    public function leastUsedFeature(): ?string
    {
        $feature = FeatureUsageLog::query()->select('feature')
            ->selectRaw('SUM(usage_count) as total')
            ->groupBy('feature')
            ->orderBy('total')
            ->value('feature');

        return is_string($feature) ? $feature : null;
    }

    public function totalInteractionsThisMonth(): int
    {
        return (int) FeatureUsageLog::query()->whereMonth('date', Date::now()->month)
            ->whereYear('date', Date::now()->year)
            ->sum('usage_count');
    }

    public function totalInteractionsAllTime(): int
    {
        return (int) FeatureUsageLog::query()->sum('usage_count');
    }

    public function featureTotalCount(?string $feature): int
    {
        if (! $feature) {
            return 0;
        }

        return (int) FeatureUsageLog::query()->where('feature', $feature)->sum('usage_count');
    }

    /** @return Collection<int, FeatureUsageBar> */
    public function featureUsageBars(): Collection
    {
        $data = FeatureUsageLog::query()->select('feature')
            ->selectRaw('SUM(usage_count) as total')
            ->groupBy('feature')
            ->orderByDesc('total')
            ->get();

        $maximumTotal = $data->max('total');
        $max = is_numeric($maximumTotal) ? (int) $maximumTotal : 1;

        return $data->map(function (FeatureUsageLog $row) use ($max): FeatureUsageBar {
            $total = Arr::integer($row->getAttributes(), 'total', 0);

            return new FeatureUsageBar(
                feature: $row->feature,
                total: $total,
                percent: round(($total / max($max, 1)) * 100),
            );
        });
    }

    public function heatmapData(): FeatureUsageHeatmap
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
            ->groupBy(fn (FeatureUsageLog $log): string => $log->feature.'|'.$log->date->toDateString());

        $maximumCount = $logs->max(
            fn (Collection $group): mixed => $group->sum('usage_count'),
        );
        $maxCount = is_numeric($maximumCount) ? (int) $maximumCount : 1;

        $rows = [];
        foreach ($features as $feature) {
            $cells = [];
            foreach ($days as $day) {
                $key = $feature.'|'.$day->toDateString();
                $count = isset($logs[$key])
                    ? Arr::integer(['count' => $logs[$key]->sum('usage_count')], 'count', 0)
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

        return new FeatureUsageHeatmap(
            days: array_values($days->map(fn (Carbon $d): string => $d->format('M d'))->all()),
            rows: $rows,
        );
    }

    /** @return Collection<int, FeatureTenantUsage> */
    public function featureTenantBreakdown(string $feature): Collection
    {
        $rows = FeatureUsageLog::query()->select('tenant_id')
            ->selectRaw('SUM(usage_count) as total')
            ->where('feature', $feature)
            ->groupBy('tenant_id')
            ->orderByDesc('total')
            ->limit(20)
            ->get();

        $tenants = Tenant::query()
            ->whereIn('id', $rows->pluck('tenant_id')->all())
            ->get(['id', 'store_name', 'name'])
            ->keyBy('id');

        return $rows->map(function (FeatureUsageLog $row) use ($tenants): FeatureTenantUsage {
            $tenant = $tenants->get($row->tenant_id);
            $storeName = $tenant?->getAttribute('store_name');
            $name = $tenant?->getAttribute('name');

            return new FeatureTenantUsage(
                tenantId: $row->tenant_id,
                name: is_string($storeName)
                    ? $storeName
                    : (is_string($name) ? $name : $row->tenant_id),
                total: (int) $row->total,
            );
        });
    }
}
