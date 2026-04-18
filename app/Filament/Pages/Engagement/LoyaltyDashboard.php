<?php

namespace App\Filament\Pages\Engagement;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Services\Loyalty\LoyaltyAnalytics;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettings;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Pennant\Feature;

class LoyaltyDashboard extends Page
{
    use RequiresManagerRole;
    use ShowsUpgradeBadge;

    public static function canAccess(): bool
    {
        return static::hasManagerAccess() && Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrophy;

    protected static ?string $navigationLabel = 'Loyalty Program';

    protected static string|\UnitEnum|null $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 13;

    protected string $view = 'filament.pages.engagement.loyalty-dashboard';

    public bool $loyaltyEnabled;

    public string $programName;

    public function mount(): void
    {
        $settings = app(TenantSettings::class);
        $this->loyaltyEnabled = $settings->loyalty->enabled;
        $this->programName = $settings->loyalty->programName;
    }

    public function toggleLoyalty(): void
    {
        $this->loyaltyEnabled = ! $this->loyaltyEnabled;
        app(SettingsManager::class)->set('loyalty_enabled', $this->loyaltyEnabled ? '1' : '0');
    }

    public function getTotalPointsIssuedProperty(): int
    {
        return $this->analytics()->metrics()->totalIssued;
    }

    public function getTotalPointsRedeemedProperty(): int
    {
        return $this->analytics()->metrics()->totalRedeemed;
    }

    public function getActiveMembersProperty(): int
    {
        return $this->analytics()->metrics()->activeMembers;
    }

    public function getAvailableRewardsCountProperty(): int
    {
        return $this->analytics()->metrics()->availableRewards;
    }

    /** @return Collection<int, Customer> */
    public function getTopCustomersProperty(): Collection
    {
        return $this->analytics()->topCustomers();
    }

    /** @return Collection<int, LoyaltyPoint> */
    public function getRecentActivityProperty(): Collection
    {
        return $this->analytics()->recentActivity();
    }

    private function analytics(): LoyaltyAnalytics
    {
        return resolve(LoyaltyAnalytics::class);
    }
}
