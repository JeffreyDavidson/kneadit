<?php

namespace App\Services;

use App\Reports\CustomerReport;
use App\Reports\FinancialReport;
use App\Reports\InventoryReport;
use App\Reports\ProductReport;
use App\Reports\SalesReport;
use App\ValueObjects\DateRange;

class ReportService
{
    public function __construct(
        protected SalesReport $salesReport,
        protected CustomerReport $customerReport,
        protected ProductReport $productReport,
        protected FinancialReport $financialReport,
        protected InventoryReport $inventoryReport,
    ) {}

    /** @return array<string, mixed> */
    public function salesReport(string $startDate, string $endDate): array
    {
        return $this->salesReport->generate(DateRange::fromStrings($startDate, $endDate));
    }

    /** @return array<string, mixed> */
    public function customerReport(string $startDate, string $endDate): array
    {
        return $this->customerReport->generate(DateRange::fromStrings($startDate, $endDate));
    }

    /** @return array<string, mixed> */
    public function productPerformanceReport(string $startDate, string $endDate): array
    {
        return $this->productReport->generate(DateRange::fromStrings($startDate, $endDate));
    }

    /** @return array<string, mixed> */
    public function financialSummary(int $year): array
    {
        return $this->financialReport->generate($year);
    }

    /** @return array<string, mixed> */
    public function inventoryReport(): array
    {
        return $this->inventoryReport->generate();
    }
}
