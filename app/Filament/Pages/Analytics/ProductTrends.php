<?php

namespace App\Filament\Pages\Analytics;

use App\Enums\Platform\SubscriptionTier;
use App\Enums\Staff\UserRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Services\Analytics\ProductTrendsService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Laravel\Pennant\Feature;
use Livewire\Attributes\Url;

class ProductTrends extends Page
{
    use ShowsUpgradeBadge;

    protected string $view = 'filament.pages.analytics.product-trends';

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

    protected static ?string $title = 'Product Trends';

    protected static ?string $navigationLabel = 'Product Trends';

    protected static ?int $navigationSort = 6;

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

    #[Url]
    public int $month = 0;

    #[Url]
    public int $year = 0;

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
