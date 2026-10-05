<?php

namespace App\Queries\Platform;

use App\DataTransferObjects\Settings\BrandingSettings;
use App\Models\Platform\Tenant;
use Illuminate\Support\Collection;

class ActiveBakeriesQuery
{
    /**
     * Get all active, unpaused tenants with storefronts enabled.
     *
     * @return Collection<int, array{name: string, url: non-falsy-string, color: string}>
     */
    public static function get(): Collection
    {
        return Tenant::query()
            ->where('is_active', true)
            ->where('storefront_enabled', true)
            ->whereNull('paused_at')
            ->with('domains')
            ->get()
            ->map(fn (Tenant $t): array => [
                'name' => (string) ($t->store_name ?? $t->name),
                'url' => 'http://'.$t->domains->first()?->domain,
                'color' => BrandingSettings::safeColor($t->brand_color_primary),
            ])
            ->values();
    }
}
