<?php

namespace App\Filament\Pages\Platform;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class UpgradePlan extends Page
{
    use RequiresManagerRole;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpCircle;

    protected static ?string $navigationLabel = 'Upgrade Plan';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected static ?string $title = 'Upgrade Your Plan';

    protected string $view = 'filament.pages.platform.upgrade-plan';

    public string $currentPlan = SubscriptionTier::Starter->value;

    /** @var array<string, mixed> */
    public array $plans = [];

    public function mount(): void
    {
        $this->currentPlan = tenant()->plan->value ?? SubscriptionTier::Starter->value;

        $this->plans = [
            SubscriptionTier::Starter->value => [
                'name' => 'Starter',
                'price' => 9,
                'features' => [
                    'Products & Categories',
                    'Orders & Customers',
                    'Quick Order',
                    'Storefront (menu, ordering, contact)',
                    'Order Tracking',
                    'Dashboard with Stats',
                    'Baking Sheet',
                    'Settings & Onboarding',
                ],
            ],
            SubscriptionTier::Growth->value => [
                'name' => 'Growth',
                'price' => 19,
                'features' => [
                    'Everything in Starter, plus:',
                    'Coupons & Discounts',
                    'Gift Cards',
                    'Customer Favorites',
                    'Reviews Management',
                    'Email Notifications',
                    'Delivery Management',
                    'Recipes & Prep Planner',
                    'Order Calendar',
                    'Customer Directory & CRM',
                    'Finance Tracking (Expenses, Income)',
                    'Printable Menu & QR Codes',
                ],
            ],
            SubscriptionTier::Pro->value => [
                'name' => 'Pro',
                'price' => 29,
                'features' => [
                    'Everything in Growth, plus:',
                    'Email Marketing Campaigns',
                    'Loyalty Rewards Program',
                    'Social Media Scheduler',
                    'Storefront Analytics',
                    'Ingredient Inventory',
                    'Product Import/Export (CSV)',
                    'Storefront Themes',
                    'Customer Photo Gallery',
                    'Seasonal Items & Holiday Planning',
                    'Schedule Manager & Blocked Dates',
                    'Instagram Caption Generator',
                    'Price Suggestions & Product Trends',
                    'Profit & Review Analytics',
                    'Shopping List & Delivery Route Planner',
                ],
            ],
        ];
    }

    public function redirectToBilling(): void
    {
        $this->redirect(tenant()?->billingPortalUrl() ?? '#');
    }
}
