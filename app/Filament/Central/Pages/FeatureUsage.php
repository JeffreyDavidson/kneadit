<?php

namespace App\Filament\Central\Pages;

use App\DataTransferObjects\Platform\FeatureTenantUsage;
use App\DataTransferObjects\Platform\FeatureUsageBar;
use App\Queries\Platform\FeatureUsageQuery;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use UnitEnum;

class FeatureUsage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCursorArrowRays;

    protected static string|UnitEnum|null $navigationGroup = 'Insights';

    protected static ?int $navigationSort = 5;

    protected static ?string $title = 'Feature Usage';

    protected string $view = 'filament.central.pages.feature-usage';

    public ?string $selectedFeature = null;

    public function getHasData(): bool
    {
        return resolve(FeatureUsageQuery::class)->hasData();
    }

    public function getMostUsedFeature(): ?string
    {
        return resolve(FeatureUsageQuery::class)->mostUsedFeature();
    }

    public function getLeastUsedFeature(): ?string
    {
        return resolve(FeatureUsageQuery::class)->leastUsedFeature();
    }

    public function getTotalInteractionsThisMonth(): int
    {
        return resolve(FeatureUsageQuery::class)->totalInteractionsThisMonth();
    }

    public function getTotalInteractionsAllTime(): int
    {
        return resolve(FeatureUsageQuery::class)->totalInteractionsAllTime();
    }

    public function getFeatureTotalCount(?string $feature): int
    {
        return resolve(FeatureUsageQuery::class)->featureTotalCount($feature);
    }

    /** @return Collection<int, array{feature: string, total: int, percent: float}> */
    public function getFeatureUsageBars(): Collection
    {
        return resolve(FeatureUsageQuery::class)->featureUsageBars()->map(
            static fn (FeatureUsageBar $bar): array => $bar->toArray(),
        );
    }

    /** @return array<string, mixed> */
    public function getHeatmapData(): array
    {
        return resolve(FeatureUsageQuery::class)->heatmapData()->toArray();
    }

    public function selectFeature(?string $feature): void
    {
        $this->selectedFeature = $this->selectedFeature === $feature ? null : $feature;
    }

    /** @return Collection<int, array{tenant_id: string, name: string, total: int}> */
    public function getFeatureTenantBreakdown(): Collection
    {
        if (! $this->selectedFeature) {
            return new Collection;
        }

        return resolve(FeatureUsageQuery::class)->featureTenantBreakdown($this->selectedFeature)->map(
            static fn (FeatureTenantUsage $row): array => $row->toArray(),
        );
    }

    public function formatFeatureName(string $feature): string
    {
        return Str::replace('_', ' ', ucfirst($feature));
    }
}
