<?php

namespace App\Filament\Pages;

use App\Services\ReportService;
use App\Traits\HasPlanGating;
use Filament\Pages\Page;

class ReportsCenter extends Page
{
    use HasPlanGating;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-pie';
    protected static ?string $navigationLabel = 'Reports';
    protected static string|\UnitEnum|null $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 4;
    protected string $view = 'filament.pages.reports-center';
    protected static string $requiredPlan = 'growth';

    public string $activeReport = '';
    public string $startDate = '';
    public string $endDate = '';
    public int $selectedYear;
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
        $service = app(ReportService::class);

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
