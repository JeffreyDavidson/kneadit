<?php

namespace App\Filament\Pages;

use App\Filament\Traits\RequiresRole;
use App\Traits\HasPlanGating;
use Filament\Pages\Page;

class UpgradePlan extends Page
{
    use HasPlanGating, RequiresRole;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-circle';

    protected static ?string $navigationLabel = 'Upgrade Plan';

    protected static ?string $title = 'Upgrade Your Plan';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.upgrade-plan';

    public string $currentPlan = 'starter';

    public array $plans = [];

    public function mount(): void
    {
        $this->currentPlan = tenant()?->plan ?? 'starter';

        $this->plans = [
            'starter' => [
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
            'growth' => [
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
            'pro' => [
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
