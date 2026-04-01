<?php

namespace App\Filament\Pages\Analytics;

use App\Enums\SubscriptionTier;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Services\Reporting\ReportService;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Laravel\Pennant\Feature;

class ReportsCenter extends Page
{
    use ShowsUpgradeBadge;

    public static function canAccess(): bool
    {
        return Feature::active('growth-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Growth;
    }

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartPie;

    protected static ?string $navigationLabel = 'Reports';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 14;

    protected string $view = 'filament.pages.reports-center';

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
        $service = resolve(ReportService::class);

        $this->reportData = match ($type) {
            'sales' => $service->salesReport($this->startDate, $this->endDate),
            'customers' => $service->customerReport($this->startDate, $this->endDate),
            'products' => $service->productPerformanceReport($this->startDate, $this->endDate),
            'financial' => $service->financialSummary($this->selectedYear),
            'inventory' => $service->inventoryReport(),
            default => [],
        };
    }

    public function exportCsv(): void
    {
        $this->dispatch('export-csv', data: $this->reportData, type: $this->activeReport);
    }
}
