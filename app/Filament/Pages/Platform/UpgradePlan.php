<?php

namespace App\Filament\Pages\Platform;

use App\Actions\Platform\CreateBillingHandoffToken;
use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Models\Platform\Tenant;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;

class UpgradePlan extends Page
{
    use RequiresManagerRole;

    #[\Override]
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpCircle;

    #[\Override]
    protected static ?string $navigationLabel = 'Upgrade Plan';

    #[\Override]
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    #[\Override]
    protected static ?string $title = 'Upgrade Your Plan';

    #[\Override]
    protected string $view = 'filament.pages.platform.upgrade-plan';

    public string $currentPlan = SubscriptionTier::Starter->value;

    /** @var array<string, mixed> */
    public array $plans = [];

    public function mount(): void
    {
        $this->currentPlan = $this->tenant()->effective_plan->value;

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

    /** @return array<int, Action> */
    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('manageBilling')
                ->label('Manage billing')
                ->icon(Heroicon::OutlinedCreditCard)
                ->authorize('manage-billing')
                ->visible(fn (): bool => $this->hasLinkedOwner())
                ->action(function (CreateBillingHandoffToken $createToken): void {
                    $this->redirect($createToken($this->tenant()));
                }),
        ];
    }

    /** Whether the Upgrade buttons can hand the signed-in owner off to central billing. */
    public function canManageBilling(): bool
    {
        return Gate::allows('manage-billing') && $this->hasLinkedOwner();
    }

    /** A bakery with no linked owner account has nobody to sign in to billing, so billing is not offered. */
    private function hasLinkedOwner(): bool
    {
        return $this->tenant()->user_id !== null;
    }

    private function tenant(): Tenant
    {
        $tenant = tenant();

        if (! $tenant instanceof Tenant) {
            throw new \LogicException('A tenant must be initialized to manage billing.');
        }

        return $tenant;
    }
}
