<?php

namespace App\Filament\Pages\Analytics;

use App\Enums\Platform\SubscriptionTier;
use App\Enums\Staff\UserRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Queries\Analytics\StorefrontAnalyticsQuery;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Pennant\Feature;

class StorefrontAnalytics extends Page
{
    use ShowsUpgradeBadge;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->hasMinRole(UserRole::Manager)) {
            return false;
        }

        return Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static ?string $navigationLabel = 'Storefront Analytics';

    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.analytics.storefront-analytics';

    public string $period = 'week';

    public function mount(): void
    {
        $this->period = request()->query('period', 'week');
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

    /** @return Collection<int, mixed> */
    public function getPageViewsChart(): Collection
    {
        return $this->query()->pageViewsByPage();
    }

    /** @return Collection<int, mixed> */
    public function getDailyTrend(): Collection
    {
        return $this->query()->dailyTrend((int) config('analytics.trend_days', 30));
    }

    /** @return Collection<int, mixed> */
    public function getTopProducts(): Collection
    {
        return $this->query()->topProducts();
    }

    /** @return array<int, array<string, mixed>> */
    public function getConversionFunnel(): array
    {
        return $this->query()->conversionFunnel();
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
