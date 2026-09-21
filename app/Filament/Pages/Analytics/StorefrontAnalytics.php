<?php

namespace App\Filament\Pages\Analytics;

use App\DataTransferObjects\Analytics\ConversionFunnelStep;
use App\DataTransferObjects\Analytics\DailyPageViewCount;
use App\DataTransferObjects\Analytics\PageViewCount;
use App\DataTransferObjects\Analytics\TopViewedProduct;
use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Queries\Analytics\StorefrontAnalyticsQuery;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Laravel\Pennant\Feature;

class StorefrontAnalytics extends Page
{
    use RequiresManagerRole;
    use ShowsUpgradeBadge;

    #[\Override]
    public static function canAccess(): bool
    {
        return static::hasManagerAccess() && Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    #[\Override]
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    #[\Override]
    protected static ?string $navigationLabel = 'Storefront Analytics';

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    #[\Override]
    protected static ?int $navigationSort = 10;

    #[\Override]
    protected string $view = 'filament.pages.analytics.storefront-analytics';

    public string $period = 'week';

    public function mount(): void
    {
        $this->period = request()->string('period', 'week')->toString();
    }

    public function getTotalViews(): int
    {
        return $this->query()->totalViews();
    }

    public function getUniqueVisitors(): int
    {
        return $this->query()->uniqueVisitors();
    }

    public function getMostPopularPage(): string
    {
        return $this->query()->mostPopularPage();
    }

    public function getConversionRate(): float
    {
        return $this->query()->conversionRate();
    }

    /** @return Collection<int, PageViewCount> */
    public function getPageViewsChart(): Collection
    {
        return $this->query()->pageViewsByPage();
    }

    /** @return Collection<int, DailyPageViewCount> */
    public function getDailyTrend(): Collection
    {
        return $this->query()->dailyTrend(Config::integer('analytics.trend_days', 30));
    }

    /** @return Collection<int, TopViewedProduct> */
    public function getTopProducts(): Collection
    {
        return $this->query()->topProducts();
    }

    /** @return list<array{label: string, count: int, percentage: float, dropoff: float|null}> */
    public function getConversionFunnel(): array
    {
        return array_map(
            static fn (ConversionFunnelStep $step): array => $step->toArray(),
            $this->query()->conversionFunnel(),
        );
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;
    }

    private function query(): StorefrontAnalyticsQuery
    {
        return new StorefrontAnalyticsQuery($this->getStartDate());
    }

    private function getStartDate(): ?Carbon
    {
        return match ($this->period) {
            'today' => today(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'all' => null,
            default => now()->startOfWeek(),
        };
    }
}
