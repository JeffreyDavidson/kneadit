<?php

namespace App\Filament\Pages;

use App\Enums\LoyaltyPointType;
use App\Enums\SubscriptionTier;
use App\Enums\UserRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Customer;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltyReward;
use App\Models\Setting;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Laravel\Pennant\Feature;

class LoyaltyDashboard extends Page
{
    use ShowsUpgradeBadge;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->hasMinRole(UserRole::Manager)) {
            return false;
        }

        return Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

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
        return (int) LoyaltyPoint::query()->where('type', LoyaltyPointType::Earned)->sum('points');
    }

    public function getTotalPointsRedeemedProperty(): int
    {
        return (int) LoyaltyPoint::query()->where('type', LoyaltyPointType::Redeemed)->sum('points');
    }

    public function getActiveMembersProperty(): int
    {
        return LoyaltyPoint::query()->distinct('customer_id')->count('customer_id');
    }

    public function getAvailableRewardsCountProperty(): int
    {
        return LoyaltyReward::query()->where('is_active', true)->count();
    }

    /** @return Collection<int, Model> */
    public function getTopCustomersProperty(): Collection
    {
        return Customer::query()->select('customers.*')
            ->join('loyalty_points', 'customers.id', '=', 'loyalty_points.customer_id')
            ->groupBy('customers.id')
            ->selectRaw("SUM(CASE WHEN loyalty_points.type = '".LoyaltyPointType::Earned->value."' THEN loyalty_points.points ELSE 0 END) - SUM(CASE WHEN loyalty_points.type = '".LoyaltyPointType::Redeemed->value."' THEN loyalty_points.points ELSE 0 END) as balance")
            ->selectRaw("SUM(CASE WHEN loyalty_points.type = '".LoyaltyPointType::Earned->value."' THEN loyalty_points.points ELSE 0 END) as total_earned")
            ->orderByDesc('balance')
            ->limit(10)
            ->get();
    }

    /** @return Collection<int, LoyaltyPoint> */
    public function getRecentActivityProperty(): Collection
    {
        return LoyaltyPoint::with('customer')
            ->latest('created_at')
            ->limit(15)
            ->get();
    }
}
