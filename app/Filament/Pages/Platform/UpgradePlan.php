<?php

namespace App\Filament\Pages\Platform;

use App\Enums\Staff\UserRole;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class UpgradePlan extends Page
{
    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->hasMinRole(UserRole::Manager)) {
            return false;
        }

        return true;
    }

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpCircle;

    protected static ?string $navigationLabel = 'Upgrade Plan';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected static ?string $title = 'Upgrade Your Plan';

    protected string $view = 'filament.pages.platform.upgrade-plan';

    public string $currentPlan = 'starter';

    /** @var array<string, mixed> */
    public array $plans = [];

    public function mount(): void
    {
        $this->currentPlan = tenant()->plan?->value ?? 'starter';

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
