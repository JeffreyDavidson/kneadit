<?php

namespace App\Queries\Platform;

use App\DataTransferObjects\Settings\BrandingSettings;
use App\Models\Platform\Tenant;
use App\Services\Tenants\TenantUrlGenerator;
use Illuminate\Support\Collection;

class ActiveBakeriesQuery
{
    /**
     * Get all active, unpaused tenants with storefronts enabled.
     *
     * @return Collection<int, array{name: string, url: string, color: string}>
     */
    public static function get(): Collection
    {
        $tenantUrls = resolve(TenantUrlGenerator::class);

        return Tenant::query()
            ->where('is_active', true)
            ->where('storefront_enabled', true)
            ->whereNull('paused_at')
            ->get()
            ->map(fn (Tenant $t): array => [
                'name' => (string) ($t->store_name ?? $t->name),
                'url' => $tenantUrls->primaryStorefront($t),
                'color' => BrandingSettings::safeColor($t->brand_color_primary),
            ])
            ->values();
    }
}
