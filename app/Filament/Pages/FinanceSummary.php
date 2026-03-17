<?php

namespace App\Filament\Pages;

use App\Enums\PaymentStatus;
use App\Filament\Traits\RequiresRole;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Order;
use App\Models\Setting;
use App\Traits\HasPlanGating;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FinanceSummary extends Page
{
    use HasPlanGating, RequiresRole;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Finance Summary';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.finance-summary';

    protected static string $requiredPlan = 'growth';

    public int $selectedYear;

    public float $totalRevenue = 0;

    public float $totalExpenses = 0;

    public float $netProfit = 0;

    public float $revenueCap = 250000;

    public float $revenueCapProgress = 0;

    public Collection $monthlyBreakdown;

    public Collection $expenseBreakdown;

    public float $cogsAmount = 0;

    public float $cogsPercentage = 0;

    public function mount()
    {
        $this->selectedYear = now()->year;
        $this->revenueCap = (float) Setting::get('revenue_cap', 250000);
        $this->loadFinancialData();
    }

    public function loadFinancialData()
    {
        $this->calculateYearlyTotals();
        $this->calculateMonthlyBreakdown();
        $this->calculateExpenseBreakdown();
        $this->calculateCOGS();
        $this->calculateRevenueCapProgress();
    }

    private function calculateYearlyTotals()
    {
        // Total revenue from paid orders
        $this->totalRevenue = Order::whereYear('delivery_date', $this->selectedYear)
            ->where('payment_status', PaymentStatus::Paid)
            ->sum('total');

        // Total expenses for the year
        $this->totalExpenses = Expense::whereYear('date', $this->selectedYear)
            ->sum('amount');

        // Add income to revenue
        $totalIncome = Income::whereYear('date', $this->selectedYear)
            ->sum('amount');

        $this->totalRevenue += $totalIncome;

        // Net profit
        $this->netProfit = $this->totalRevenue - $this->totalExpenses;
    }

    private function calculateMonthlyBreakdown()
    {
        $this->monthlyBreakdown = collect();

        for ($month = 1; $month <= 12; $month++) {
            $monthRevenue = Order::whereYear('delivery_date', $this->selectedYear)
                ->whereMonth('delivery_date', $month)
                ->where('payment_status', PaymentStatus::Paid)
                ->sum('total');

            $monthIncome = Income::whereYear('date', $this->selectedYear)
                ->whereMonth('date', $month)
                ->sum('amount');

            $monthExpenses = Expense::whereYear('date', $this->selectedYear)
                ->whereMonth('date', $month)
                ->sum('amount');

            $totalMonthRevenue = $monthRevenue + $monthIncome;

            $this->monthlyBreakdown->push([
                'month' => $month,
                'month_name' => date('F', mktime(0, 0, 0, $month, 1)),
                'revenue' => $totalMonthRevenue,
                'expenses' => $monthExpenses,
                'net' => $totalMonthRevenue - $monthExpenses,
            ]);
        }
    }

    private function calculateExpenseBreakdown()
    {
        $totalExpenses = Expense::whereYear('date', $this->selectedYear)->sum('amount');

        if ($totalExpenses == 0) {
            $this->expenseBreakdown = collect();

            return;
        }

        $this->expenseBreakdown = Expense::whereYear('date', $this->selectedYear)
            ->select('category', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('category')
            ->get()
            ->map(function ($expense) use ($totalExpenses) {
                $categoryName = Expense::CATEGORIES[$expense->category] ?? ucfirst($expense->category);

                return [
                    'category' => $categoryName,
                    'amount' => $expense->total_amount,
                    'percentage' => round(($expense->total_amount / $totalExpenses) * 100, 1),
                ];
            })
            ->sortByDesc('amount');
    }

    private function calculateCOGS()
    {
        // COGS = ingredients + packaging expenses
        $this->cogsAmount = Expense::whereYear('date', $this->selectedYear)
            ->whereIn('category', ['ingredients', 'packaging'])
            ->sum('amount');

        if ($this->totalExpenses > 0) {
            $this->cogsPercentage = round(($this->cogsAmount / $this->totalExpenses) * 100, 1);
        }
    }

    private function calculateRevenueCapProgress()
    {
        if ($this->revenueCap > 0) {
            $this->revenueCapProgress = min(($this->totalRevenue / $this->revenueCap) * 100, 100);
        }
    }

    public function updatedSelectedYear()
    {
        $this->loadFinancialData();
    }

    protected function getActions(): array
    {
        return [
            Action::make('export')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->action(fn () => $this->dispatch('export-finance-summary')),
        ];
    }
}
