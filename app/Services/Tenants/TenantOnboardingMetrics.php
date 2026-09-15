<?php

namespace App\Services\Tenants;

use App\DataTransferObjects\Settings\BrandingSettings;
use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Platform\Tenant;

class TenantOnboardingMetrics
{
    public const int TOTAL_CHECKS = 7;

    public function __construct(
        private TenancyManager $tenancy,
    ) {}

    /** @return array<string, bool> */
    public function checks(Tenant $tenant): array
    {
        return [
            'store_name' => filled($tenant->store_name),
            'store_logo' => filled($tenant->store_logo),
            'storefront_enabled' => $tenant->storefront_enabled,
            'brand_customized' => filled($tenant->brand_color_primary)
                && $tenant->brand_color_primary !== BrandingSettings::DEFAULT_BRAND_COLOR,
            'has_products' => $tenant->onboarding_products_count > 0,
            'has_categories' => $tenant->onboarding_categories_count > 0,
            'has_orders' => $tenant->onboarding_orders_count > 0,
        ];
    }

    public function completed(Tenant $tenant): int
    {
        return collect($this->checks($tenant))->filter()->count();
    }

    public function sync(Tenant $tenant): void
    {
        $counts = $this->tenancy->withinTenant($tenant, fn (): array => [
            'onboarding_products_count' => Product::query()->count(),
            'onboarding_categories_count' => Category::query()->count(),
            'onboarding_orders_count' => Order::query()->count(),
        ]);

        $tenant->forceFill([
            ...$counts,
            'onboarding_metrics_synced_at' => now(),
        ])->save();
    }
}
