<?php

namespace App\Filament\Pages;

use App\Enums\SubscriptionTier;
use App\Enums\UserRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Setting;
use App\Services\FinancialCalculator;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Pennant\Feature;

class FinanceSummary extends Page
{
    use ShowsUpgradeBadge;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->hasMinRole(UserRole::Manager)) {
            return false;
        }

        return Feature::active('growth-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Growth;
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Finance Summary';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.finance-summary';

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
        $this->revenueCap = (float) Setting::get('revenue_cap', 250000);
        $this->loadFinancialData();
    }

    public function loadFinancialData(): void
    {
        $data = resolve(FinancialCalculator::class)->calculate($this->selectedYear);

        $this->totalRevenue = $data['totalRevenue'];
        $this->totalExpenses = $data['totalExpenses'];
        $this->netProfit = $data['netProfit'];
        $this->monthlyBreakdown = $data['monthlyBreakdown'];
        $this->expenseBreakdown = $data['expenseBreakdown'];
        $this->cogsAmount = $data['cogsAmount'];
        $this->cogsPercentage = $data['cogsPercentage'];

        $this->revenueCapProgress = $this->revenueCap > 0
            ? min(($this->totalRevenue / $this->revenueCap) * 100, 100)
            : 0;
    }

    public function updatedSelectedYear(): void
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
