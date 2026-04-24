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

    public bool $tiersEnabled;

    public int $tierSilverThreshold;

    public int $tierGoldThreshold;

    public int $tierPlatinumThreshold;

    public bool $tierPerksEnabled;

    public float $tierSilverMultiplier;

    public bool $tierSilverFreeDelivery;

    public float $tierGoldMultiplier;

    public bool $tierGoldFreeDelivery;

    public float $tierPlatinumMultiplier;

    public bool $tierPlatinumFreeDelivery;

    public function mount(): void
    {
        $settings = resolve(TenantSettings::class);
        $this->loyaltyEnabled = $settings->loyalty->enabled;
        $this->programName = $settings->loyalty->programName;
        $this->tiersEnabled = $settings->loyalty->tiersEnabled;
        $this->tierSilverThreshold = $settings->loyalty->tierSilverThreshold;
        $this->tierGoldThreshold = $settings->loyalty->tierGoldThreshold;
        $this->tierPlatinumThreshold = $settings->loyalty->tierPlatinumThreshold;
        $this->tierPerksEnabled = $settings->loyalty->tierPerksEnabled;
        $this->tierSilverMultiplier = $settings->loyalty->tierSilverMultiplier;
        $this->tierSilverFreeDelivery = $settings->loyalty->tierSilverFreeDelivery;
        $this->tierGoldMultiplier = $settings->loyalty->tierGoldMultiplier;
        $this->tierGoldFreeDelivery = $settings->loyalty->tierGoldFreeDelivery;
        $this->tierPlatinumMultiplier = $settings->loyalty->tierPlatinumMultiplier;
        $this->tierPlatinumFreeDelivery = $settings->loyalty->tierPlatinumFreeDelivery;
    }

    public function toggleLoyalty(): void
    {
        $this->loyaltyEnabled = ! $this->loyaltyEnabled;
        resolve(SettingsManager::class)->set('loyalty_enabled', $this->loyaltyEnabled ? '1' : '0');
    }

    public function saveTiers(): void
    {
        $manager = resolve(SettingsManager::class);
        $manager->set('loyalty_tiers_enabled', $this->tiersEnabled ? '1' : '0');
        $manager->set('loyalty_tier_silver_threshold', (string) $this->tierSilverThreshold);
        $manager->set('loyalty_tier_gold_threshold', (string) $this->tierGoldThreshold);
        $manager->set('loyalty_tier_platinum_threshold', (string) $this->tierPlatinumThreshold);
    }

    public function saveTierPerks(): void
    {
        $manager = resolve(SettingsManager::class);
        $manager->set('loyalty_tier_perks_enabled', $this->tierPerksEnabled ? '1' : '0');
        $manager->set('loyalty_tier_silver_multiplier', (string) $this->tierSilverMultiplier);
        $manager->set('loyalty_tier_silver_free_delivery', $this->tierSilverFreeDelivery ? '1' : '0');
        $manager->set('loyalty_tier_gold_multiplier', (string) $this->tierGoldMultiplier);
        $manager->set('loyalty_tier_gold_free_delivery', $this->tierGoldFreeDelivery ? '1' : '0');
        $manager->set('loyalty_tier_platinum_multiplier', (string) $this->tierPlatinumMultiplier);
        $manager->set('loyalty_tier_platinum_free_delivery', $this->tierPlatinumFreeDelivery ? '1' : '0');
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
