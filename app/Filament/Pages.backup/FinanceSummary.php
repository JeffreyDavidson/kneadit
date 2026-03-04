<?php

namespace App\Filament\Pages;

use App\Models\Expense;
use App\Models\Income;
use App\Models\Setting;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use BackedEnum;

class FinanceSummary extends Page
{
    protected string $view = 'filament.pages.finance-summary';
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlineChartPie;
    
    protected static string|BackedEnum|null $navigationGroup = 'Finance';
    
    protected static ?string $title = 'Finance Summary';
    
    public ?int $selectedYear = null;
    public ?string $selectedMonth = null;
    
    public array $yearlyData = [];
    public array $monthlyData = [];
    public float $totalRevenue = 0;
    public float $revenueCap = 250000; // FL cottage food cap
    
    public function mount(): void
    {
        $this->selectedYear = now()->year;
        $this->selectedMonth = now()->format('Y-m');
        $this->loadFinanceData();
    }
    
    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                EmbeddedSchema::make()
                    ->form(function (Form $form) {
                        return $form
                            ->schema([
                                Section::make('Filters')
                                    ->components([
                                        Grid::make(2)
                                            ->components([
                                                Select::make('selectedYear')
                                                    ->label('Year')
                                                    ->options($this->getYearOptions())
                                                    ->default(now()->year)
                                                    ->reactive()
                                                    ->afterStateUpdated(fn () => $this->loadFinanceData()),
                                                    
                                                DatePicker::make('selectedMonth')
                                                    ->label('Month for Details')
                                                    ->displayFormat('Y-m')
                                                    ->format('Y-m')
                                                    ->default(now()->format('Y-m'))
                                                    ->reactive()
                                                    ->afterStateUpdated(fn () => $this->loadFinanceData()),
                                            ]),
                                    ]),
                            ]);
                    })
                    ->statePath(''),
            ]);
    }
    
    public function loadFinanceData(): void
    {
        $this->yearlyData = $this->getYearlyData();
        $this->monthlyData = $this->getMonthlyData();
        $this->totalRevenue = $this->getTotalRevenue();
        
        // Get revenue cap from settings
        $revenueCap = Setting::where('key', 'revenue_cap')->first()?->value;
        if ($revenueCap) {
            $this->revenueCap = (float) $revenueCap;
        }
    }
    
    private function getYearOptions(): array
    {
        $currentYear = now()->year;
        $years = [];
        
        for ($year = $currentYear - 5; $year <= $currentYear + 1; $year++) {
            $years[$year] = $year;
        }
        
        return $years;
    }
    
    private function getYearlyData(): array
    {
        $year = $this->selectedYear ?: now()->year;
        
        $totalIncome = Income::whereYear('date', $year)->sum('amount');
        $totalExpenses = Expense::whereYear('date', $year)->sum('deductible_amount');
        
        return [
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'net_profit' => $totalIncome - $totalExpenses,
            'profit_margin' => $totalIncome > 0 ? ($totalIncome - $totalExpenses) / $totalIncome * 100 : 0,
        ];
    }
    
    private function getMonthlyData(): array
    {
        if (!$this->selectedMonth) {
            return [];
        }
        
        $date = Carbon::parse($this->selectedMonth . '-01');
        
        $totalIncome = Income::whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->sum('amount');
            
        $totalExpenses = Expense::whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->sum('deductible_amount');
        
        $incomeBySource = Income::whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->selectRaw('source, sum(amount) as total')
            ->groupBy('source')
            ->get()
            ->pluck('total', 'source')
            ->toArray();
            
        $expensesByCategory = Expense::whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->selectRaw('category, sum(deductible_amount) as total')
            ->groupBy('category')
            ->get()
            ->pluck('total', 'category')
            ->toArray();
        
        return [
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'net_profit' => $totalIncome - $totalExpenses,
            'income_by_source' => $incomeBySource,
            'expenses_by_category' => $expensesByCategory,
            'month_name' => $date->format('F Y'),
        ];
    }
    
    private function getTotalRevenue(): float
    {
        $year = $this->selectedYear ?: now()->year;
        return Income::whereYear('date', $year)->sum('amount');
    }
    
    public function getRevenueCapPercentage(): float
    {
        return $this->revenueCap > 0 ? ($this->totalRevenue / $this->revenueCap) * 100 : 0;
    }
}