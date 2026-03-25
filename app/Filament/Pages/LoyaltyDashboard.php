<?php

namespace App\Filament\Pages;

use App\Filament\Traits\RequiresRole;
use App\Models\Customer;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltyReward;
use App\Models\Setting;
use App\Traits\HasPlanGating;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;

class LoyaltyDashboard extends Page
{
    use HasPlanGating, RequiresRole;

    protected static string $requiredPlan = 'pro';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationLabel = 'Loyalty Program';

    protected static string|\UnitEnum|null $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 13;

    protected string $view = 'filament.pages.loyalty-dashboard';

    public bool $loyaltyEnabled;

    public string $programName;

    public function mount(): void
    {
        $this->loyaltyEnabled = Setting::get('loyalty_enabled', '1') === '1';
        $this->programName = Setting::get('loyalty_program_name', 'Rewards');
    }

    public function toggleLoyalty(): void
    {
        $this->loyaltyEnabled = ! $this->loyaltyEnabled;
        Setting::set('loyalty_enabled', $this->loyaltyEnabled ? '1' : '0');
    }

    public function getTotalPointsIssuedProperty(): int
    {
        return (int) LoyaltyPoint::query()->where('type', 'earned')->sum('points');
    }

    public function getTotalPointsRedeemedProperty(): int
    {
        return (int) LoyaltyPoint::query()->where('type', 'redeemed')->sum('points');
    }

    public function getActiveMembersProperty(): int
    {
        return LoyaltyPoint::query()->distinct('customer_id')->count('customer_id');
    }

    public function getAvailableRewardsCountProperty(): int
    {
        return LoyaltyReward::query()->where('is_active', true)->count();
    }

    public function getTopCustomersProperty(): Collection
    {
        return Customer::query()->select('customers.*')
            ->join('loyalty_points', 'customers.id', '=', 'loyalty_points.customer_id')
            ->groupBy('customers.id')
            ->selectRaw("SUM(CASE WHEN loyalty_points.type = 'earned' THEN loyalty_points.points ELSE 0 END) - SUM(CASE WHEN loyalty_points.type = 'redeemed' THEN loyalty_points.points ELSE 0 END) as balance")
            ->selectRaw("SUM(CASE WHEN loyalty_points.type = 'earned' THEN loyalty_points.points ELSE 0 END) as total_earned")
            ->orderByDesc('balance')
            ->limit(10)
            ->get();
    }

    public function getRecentActivityProperty(): Collection
    {
        return LoyaltyPoint::with('customer')
            ->latest('created_at')
            ->limit(15)
            ->get();
    }
}
