<?php

namespace App\Filament\Pages\Analytics;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Services\Analytics\ProductTrendsService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Date;
use Laravel\Pennant\Feature;
use Livewire\Attributes\Url;

class ProductTrends extends Page
{
    use RequiresManagerRole;
    use ShowsUpgradeBadge;

    protected string $view = 'filament.pages.analytics.product-trends';

    protected static ?string $title = 'Product Trends';

    protected static ?string $navigationLabel = 'Product Trends';

    protected static ?int $navigationSort = 6;

    #[Url]
    public int $month = 0;

    #[Url]
    public int $year = 0;

    public static function canAccess(): bool
    {
        return static::hasManagerAccess() && Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return Heroicon::OutlinedArrowTrendingUp;
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return 'Tools';
    }

    public function getBreadcrumbs(): array
    {
        return [
            '/admin' => 'Dashboard',
            static::getUrl() => 'Product Trends',
        ];
    }

    public function mount(): void
    {
        if ($this->month === 0) {
            $this->month = now()->month;
        }
        if ($this->year === 0) {
            $this->year = now()->year;
        }
    }

    public function previousMonth(): void
    {
        $date = Date::create($this->year, $this->month, 1)->subMonth();
        $this->month = $date->month;
        $this->year = $date->year;
    }

    public function nextMonth(): void
    {
        $date = Date::create($this->year, $this->month, 1)->addMonth();
        $this->month = $date->month;
        $this->year = $date->year;
    }

    /** @return array<int, mixed> */
    public function getTrendsDataProperty(): array
    {
        return resolve(ProductTrendsService::class)->calculate($this->year, $this->month);
    }

    public function getMonthLabelProperty(): string
    {
        return Date::create($this->year, $this->month, 1)->format('F Y');
    }

    public function getPrevMonthLabelProperty(): string
    {
        return Date::create($this->year, $this->month, 1)->subMonth()->format('M Y');
    }
}
