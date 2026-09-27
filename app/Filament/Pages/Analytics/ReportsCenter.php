<?php

namespace App\Filament\Pages\Analytics;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Reports\Customers\CustomerReport;
use App\Reports\Customers\RfmReport;
use App\Reports\Financial\FinancialReport;
use App\Reports\Inventory\InventoryReport;
use App\Reports\Inventory\ProductReport;
use App\Reports\Orders\SalesReport;
use App\ValueObjects\DateRange;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Config;
use Laravel\Pennant\Feature;

class ReportsCenter extends Page
{
    private const array INVENTORY_USAGE_WINDOW_OPTIONS = [7, 30, 90];

    use RequiresManagerRole;
    use ShowsUpgradeBadge;

    #[\Override]
    public static function canAccess(): bool
    {
        return static::hasManagerAccess() && Feature::active('growth-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Growth;
    }

    #[\Override]
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartPie;

    #[\Override]
    protected static ?string $navigationLabel = 'Reports';

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    #[\Override]
    protected static ?int $navigationSort = 14;

    #[\Override]
    protected string $view = 'filament.pages.analytics.reports-center';

    public string $activeReport = '';

    public string $startDate = '';

    public string $endDate = '';

    public int $selectedYear;

    public int $inventoryUsageWindowDays;

    /** @var array<string, mixed> */
    public array $reportData = [];

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->selectedYear = now()->year;
        $this->inventoryUsageWindowDays = Config::integer('analytics.inventory_usage_window_days', 30);
    }

    public function generateReport(string $type): void
    {
        $this->activeReport = $type;
        $dateRange = DateRange::fromStrings($this->startDate, $this->endDate);

        $configuredUsageWindowDays = Config::integer('analytics.inventory_usage_window_days', 30);
        $allowedUsageWindowDays = [...self::INVENTORY_USAGE_WINDOW_OPTIONS, $configuredUsageWindowDays];

        if (! in_array($this->inventoryUsageWindowDays, $allowedUsageWindowDays, true)) {
            $this->inventoryUsageWindowDays = $configuredUsageWindowDays;
        }

        $this->reportData = match ($type) {
            'sales' => resolve(SalesReport::class)->generate($dateRange)->toArray(),
            'customers' => resolve(CustomerReport::class)->generate($dateRange)->toArray(),
            'products' => resolve(ProductReport::class)->generate($dateRange)->toArray(),
            'financial' => resolve(FinancialReport::class)->generate($this->selectedYear)->toArray(),
            'inventory' => resolve(InventoryReport::class)->generate($this->inventoryUsageWindowDays)->toArray(),
            'rfm' => resolve(RfmReport::class)->generate()->toArray(),
            default => [],
        };
    }

    public function exportCsv(): void
    {
        $this->dispatch('export-csv', data: $this->reportData, type: $this->activeReport);
    }
}
