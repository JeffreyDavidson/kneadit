<?php

namespace App\Filament\Pages\Analytics;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Reports\Customers\CustomerReport;
use App\Reports\Financial\FinancialReport;
use App\Reports\Inventory\InventoryReport;
use App\Reports\Inventory\ProductReport;
use App\Reports\Orders\SalesReport;
use App\ValueObjects\DateRange;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Laravel\Pennant\Feature;

class ReportsCenter extends Page
{
    use RequiresManagerRole;
    use ShowsUpgradeBadge;

    public static function canAccess(): bool
    {
        return static::hasManagerAccess() && Feature::active('growth-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Growth;
    }

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartPie;

    protected static ?string $navigationLabel = 'Reports';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 14;

    protected string $view = 'filament.pages.analytics.reports-center';

    public string $activeReport = '';

    public string $startDate = '';

    public string $endDate = '';

    public int $selectedYear;

    /** @var array<string, mixed> */
    public array $reportData = [];

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->selectedYear = now()->year;
    }

    public function generateReport(string $type): void
    {
        $this->activeReport = $type;
        $dateRange = DateRange::fromStrings($this->startDate, $this->endDate);

        $this->reportData = match ($type) {
            'sales' => resolve(SalesReport::class)->generate($dateRange),
            'customers' => resolve(CustomerReport::class)->generate($dateRange),
            'products' => resolve(ProductReport::class)->generate($dateRange),
            'financial' => resolve(FinancialReport::class)->generate($this->selectedYear),
            'inventory' => resolve(InventoryReport::class)->generate(),
            default => [],
        };
    }

    public function exportCsv(): void
    {
        $this->dispatch('export-csv', data: $this->reportData, type: $this->activeReport);
    }
}
