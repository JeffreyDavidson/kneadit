<?php

namespace App\Http\Resources;

use App\Services\Settings\TenantSettings;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

/**
 * @property TenantSettings $resource
 *
 * @mixin TenantSettings
 */
class StoreInfoResource extends JsonApiResource
{
    public function toId(Request $request): string
    {
        // Singleton — there's always exactly one store-info per tenant.
        return 'current';
    }

    public function toType(Request $request): string
    {
        return 'store-info';
    }

    /** @return array<string, mixed> */
    public function toAttributes(Request $request): array
    {
        return [
            'store_name' => $this->resource->store->name,
            'tagline' => $this->resource->store->tagline ?? '',
            'phone' => $this->resource->store->phone ?? '',
            'email' => $this->resource->store->email ?? '',
            'address' => $this->resource->store->address ?? '',
            'logo_url' => $this->resource->store->logoUrl() ?? '',
            'colors' => [
                'primary' => $this->resource->branding->brandColorPrimary,
            ],
            'hours' => $this->resource->homepage->operatingHours,
            'social_links' => $this->resource->homepage->socialMediaLinks,
        ];
    }
}
