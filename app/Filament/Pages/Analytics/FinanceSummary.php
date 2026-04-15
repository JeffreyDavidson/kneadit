<?php

namespace App\Filament\Pages\Analytics;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Services\Financial\FinancialCalculator;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Laravel\Pennant\Feature;

class FinanceSummary extends Page
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

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Finance Summary';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.analytics.finance-summary';

    public int $selectedYear;

    public float $totalRevenue = 0;

    public float $totalExpenses = 0;

    public float $netProfit = 0;

    public float $revenueCap = 250000;

    public float $revenueCapProgress = 0;

    /** @var Collection<int, mixed> */
    public Collection $monthlyBreakdown;

    /** @var Collection<int, mixed> */
    public Collection $expenseBreakdown;

    public float $cogsAmount = 0;

    public float $cogsPercentage = 0;

    public function mount(): void
    {
        $this->selectedYear = now()->year;
        $this->revenueCap = (float) settings('revenue_cap', 250000);
        $this->loadFinancialData();
    }

    public function loadFinancialData(): void
    {
        $data = resolve(FinancialCalculator::class)->calculate($this->selectedYear);

        $this->totalRevenue = $data->totalRevenue;
        $this->totalExpenses = $data->totalExpenses;
        $this->netProfit = $data->netProfit;
        $this->monthlyBreakdown = $data->monthlyBreakdown->map(fn (\App\DataTransferObjects\Financial\MonthlyFinancials $m) => ['month_name' => $m->monthName, 'revenue' => $m->revenue, 'expenses' => $m->expenses, 'net' => $m->net]);
        $this->expenseBreakdown = $data->expenseBreakdown;
        $this->cogsAmount = $data->cogsAmount;
        $this->cogsPercentage = $data->cogsPercentage;

        $this->revenueCapProgress = $this->revenueCap > 0
            ? min(($this->totalRevenue / $this->revenueCap) * 100, 100)
            : 0;
    }

    public function updatedSelectedYear(): void
    {
        $this->loadFinancialData();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Export PDF')
                ->icon(Heroicon::OutlinedDocumentArrowDown)
                ->action(fn () => $this->dispatch('export-finance-summary')),
        ];
    }
}
