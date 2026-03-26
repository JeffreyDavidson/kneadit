<?php

namespace App\Filament\Central\Pages;

use App\Models\FeatureUsageLog;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use UnitEnum;

class FeatureUsage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cursor-arrow-rays';

    protected static string|UnitEnum|null $navigationGroup = 'Insights';

    protected static ?int $navigationSort = 5;

    protected static ?string $title = 'Feature Usage';

    protected string $view = 'filament.central.pages.feature-usage';

    public ?string $selectedFeature = null;

    public function getHasData(): bool
    {
        return FeatureUsageLog::query()->exists();
    }

    public function getMostUsedFeature(): ?string
    {
        $value = FeatureUsageLog::query()->select('feature')
            ->selectRaw('SUM(usage_count) as total')
            ->groupBy('feature')
            ->orderByDesc('total')
            ->value('feature');

        return is_string($value) ? $value : null;
    }

    public function getLeastUsedFeature(): ?string
    {
        $value = FeatureUsageLog::query()->select('feature')
            ->selectRaw('SUM(usage_count) as total')
            ->groupBy('feature')
            ->orderBy('total')
            ->value('feature');

        return is_string($value) ? $value : null;
    }

    public function getTotalInteractionsThisMonth(): int
    {
        return (int) FeatureUsageLog::query()->whereMonth('date', Date::now()->month)
            ->whereYear('date', Date::now()->year)
            ->sum('usage_count');
    }

    /** @return Collection<int, mixed> */
    public function getFeatureUsageBars(): Collection
    {
        $data = FeatureUsageLog::query()->select('feature')
            ->selectRaw('SUM(usage_count) as total')
            ->groupBy('feature')
            ->orderByDesc('total')
            ->get();

        $max = $data->max('total') ?: 1;

        return $data->map(fn (FeatureUsageLog $row) => [
            'feature' => $row->feature,
            'total' => (int) $row->total,
            'percent' => round(((int) $row->total / $max) * 100),
        ]);
    }

    /** @return array<string, mixed> */
    public function getHeatmapData(): array
    {
        $days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $days->push(Date::today()->subDays($i));
        }

        $features = FeatureUsageLog::query()->distinct()->pluck('feature')->sort()->values();

        $logs = FeatureUsageLog::query()->whereBetween('date', [$days->first()->toDateString(), $days->last()->toDateString()])
            ->get()
            ->groupBy(fn (FeatureUsageLog $log) => $log->feature.'|'.$log->date->toDateString());

        $maxCount = $logs->max(fn (Collection $group) => $group->sum('usage_count')) ?: 1;

        $rows = [];
        foreach ($features as $feature) {
            $cells = [];
            foreach ($days as $day) {
                $key = (string) $feature.'|'.$day->toDateString();
                $count = isset($logs[$key]) ? $logs[$key]->sum('usage_count') : 0;
                $intensity = (int) $maxCount > 0 ? $count / (int) $maxCount : 0;
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

    public function selectFeature(?string $feature): void
    {
        $this->selectedFeature = $this->selectedFeature === $feature ? null : $feature;
    }

    /** @return Collection<int, mixed> */
    public function getFeatureTenantBreakdown(): Collection
    {
        if (! $this->selectedFeature) {
            return collect();
        }

        return FeatureUsageLog::query()->select('tenant_id')
            ->selectRaw('SUM(usage_count) as total')
            ->where('feature', $this->selectedFeature)
            ->groupBy('tenant_id')
            ->orderByDesc('total')
            ->limit(20)
            ->get();
    }

    public function formatFeatureName(string $feature): string
    {
        return str_replace('_', ' ', ucfirst($feature));
    }
}
