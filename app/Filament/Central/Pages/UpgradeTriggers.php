<?php

namespace App\Filament\Central\Pages;

use App\Models\Tenant;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class UpgradeTriggers extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-circle';

    protected static string|UnitEnum|null $navigationGroup = 'Insights';

    protected static ?int $navigationSort = 6;

    protected static ?string $title = 'Plan Upgrade Triggers';

    protected string $view = 'filament.central.pages.upgrade-triggers';

    public const PLAN_LIMITS = [
        'starter' => [
            'products' => 15,
            'orders_per_month' => 50,
            'label' => 'Starter',
        ],
        'growth' => [
            'products' => 50,
            'orders_per_month' => 200,
            'label' => 'Growth',
        ],
        'pro' => [
            'products' => null,
            'orders_per_month' => null,
            'label' => 'Pro',
        ],
    ];

    public function getTenantUsageData(): Collection
    {
        $tenants = Tenant::all();
        $results = collect();

        foreach ($tenants as $tenant) {
            $plan = strtolower($tenant->plan ?? 'starter');
            $limits = self::PLAN_LIMITS[$plan] ?? self::PLAN_LIMITS['starter'];

            // Pro plan has no limits
            if ($plan === 'pro') {
                continue;
            }

            try {
                tenancy()->initialize($tenant);

                $productCount = DB::connection('tenant')->table('products')->count();
                $orderCount = DB::connection('tenant')->table('orders')
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->count();

                tenancy()->end();

                $productLimit = $limits['products'];
                $orderLimit = $limits['orders_per_month'];

                $productPercent = $productLimit ? round(($productCount / $productLimit) * 100) : 0;
                $orderPercent = $orderLimit ? round(($orderCount / $orderLimit) * 100) : 0;

                $approachingLimit = $productPercent >= 80 || $orderPercent >= 80;
                $atLimit = $productPercent >= 100 || $orderPercent >= 100;

                if ($approachingLimit) {
                    $results->push([
                        'tenant' => $tenant,
                        'name' => $tenant->name ?? $tenant->store_name ?? $tenant->id,
                        'plan' => $limits['label'],
                        'plan_key' => $plan,
                        'product_count' => $productCount,
                        'product_limit' => $productLimit,
                        'product_percent' => min($productPercent, 100),
                        'order_count' => $orderCount,
                        'order_limit' => $orderLimit,
                        'order_percent' => min($orderPercent, 100),
                        'at_limit' => $atLimit,
                        'approaching_limit' => $approachingLimit && ! $atLimit,
                    ]);
                }
            } catch (\Throwable $e) {
                tenancy()->end();
                continue;
            }
        }

        return $results->sortByDesc(fn ($t) => max($t['product_percent'], $t['order_percent']));
    }

    public function getNextPlan(string $currentPlan): ?string
    {
        return match ($currentPlan) {
            'starter' => 'Growth',
            'growth' => 'Pro',
            default => null,
        };
    }

    public function suggestUpgrade(string $tenantId): void
    {
        // Placeholder — will eventually send upgrade suggestion email
        \Filament\Notifications\Notification::make()
            ->title('Upgrade suggestion noted')
            ->body("Upgrade suggestion for tenant {$tenantId} has been queued.")
            ->success()
            ->send();
    }
}
