<?php

namespace App\Services\Tenants;

use App\Enums\Platform\SubscriptionTier;
use App\Models\Platform\Tenant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class TenantUsageService
{
    public function __construct(
        protected TenancyManager $tenancyManager,
    ) {}

    /** @return Collection<int, array<string, mixed>> */
    public function getTenantUsageData(): Collection
    {
        $results = collect();

        foreach (Tenant::all() as $tenant) {
            $tier = $tenant->plan ?? SubscriptionTier::Starter;
            $plan = $tier->value;
            $limits = config('kneadit.plans.' . $plan . '.limits', config('kneadit.plans.starter.limits'));

            if ($tier === SubscriptionTier::Pro) {
                continue;
            }

            try {
                [$productCount, $orderCount] = $this->tenancyManager->withinTenant($tenant, function () {
                    return [
                        DB::table('products')->count(),
                        DB::table('orders')
                            ->whereMonth('created_at', Date::now()->month)
                            ->whereYear('created_at', Date::now()->year)
                            ->count(),
                    ];
                });

                $productLimit = $limits['products'];
                $orderLimit = $limits['orders_per_month'];
                $productPercent = $productLimit ? round(($productCount / $productLimit) * 100) : 0;
                $orderPercent = $orderLimit ? round(($orderCount / $orderLimit) * 100) : 0;

                if ($productPercent >= 80 || $orderPercent >= 80) {
                    $results->push([
                        'tenant' => $tenant,
                        'name' => $tenant->store_name ?? $tenant->name ?? $tenant->id,
                        'plan' => config('kneadit.plans.' . $plan . '.name', ucfirst($plan)),
                        'plan_key' => $plan,
                        'product_count' => $productCount,
                        'product_limit' => $productLimit,
                        'product_percent' => min($productPercent, 100),
                        'order_count' => $orderCount,
                        'order_limit' => $orderLimit,
                        'order_percent' => min($orderPercent, 100),
                        'at_limit' => $productPercent >= 100 || $orderPercent >= 100,
                        'approaching_limit' => ! ($productPercent >= 100 || $orderPercent >= 100),
                    ]);
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return $results->sortByDesc(fn (array $t) => max($t['product_percent'], $t['order_percent']));
    }

    public function getNextPlan(string $currentPlan): ?SubscriptionTier
    {
        $tier = SubscriptionTier::tryFrom($currentPlan);

        return match ($tier) {
            SubscriptionTier::Starter => SubscriptionTier::Growth,
            SubscriptionTier::Growth => SubscriptionTier::Pro,
            default => null,
        };
    }
}
