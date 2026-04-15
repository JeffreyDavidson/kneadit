<?php

namespace App\Filament\Central\Pages;

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
        return FeatureUsageQuery::hasData();
    }

    public function getMostUsedFeature(): ?string
    {
        return FeatureUsageQuery::mostUsedFeature();
    }

    public function getLeastUsedFeature(): ?string
    {
        return FeatureUsageQuery::leastUsedFeature();
    }

    public function getTotalInteractionsThisMonth(): int
    {
        return FeatureUsageQuery::totalInteractionsThisMonth();
    }

    /** @return Collection<int, mixed> */
    public function getFeatureUsageBars(): Collection
    {
        return FeatureUsageQuery::featureUsageBars();
    }

    /** @return array<string, mixed> */
    public function getHeatmapData(): array
    {
        return FeatureUsageQuery::heatmapData();
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

        return FeatureUsageQuery::featureTenantBreakdown($this->selectedFeature);
    }

    public function formatFeatureName(string $feature): string
    {
        return Str::replace('_', ' ', ucfirst($feature));
    }
}
