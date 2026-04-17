<?php

namespace App\Queries\Platform;

use App\Models\Platform\Tenant;
use Illuminate\Support\Collection;

class ActiveBakeriesQuery
{
    /**
     * Get all active tenants with storefronts enabled.
     *
     * @return Collection<int, array{name: string, url: string, color: string}>
     */
    public static function get(): Collection
    {
        return Tenant::query()
            ->where('is_active', true)
            ->where('storefront_enabled', true)
            ->with('domains')
            ->get()
            ->map(fn (Tenant $t) => [
                'name' => (string) ($t->store_name ?? $t->name),
                'url' => 'http://' . $t->domains->first()?->domain,
                'color' => $t->brand_color_primary ?? '#d4920c',
            ])
            ->values();
    }
}
