<?php

namespace App\Http\Resources;

use App\Services\Settings\TenantSettings;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

/** @mixin TenantSettings */
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
            'store_name' => $this->resource->storeName,
            'tagline' => $this->resource->storeTagline ?? '',
            'phone' => $this->resource->storePhone ?? '',
            'email' => $this->resource->storeEmail ?? '',
            'address' => $this->resource->storeAddress ?? '',
            'logo_url' => $this->resource->storeLogoUrl() ?? '',
            'colors' => [
                'primary' => $this->resource->branding->brandColorPrimary,
            ],
            'hours' => $this->resource->homepage->operatingHours,
            'social_links' => $this->resource->homepage->socialMediaLinks,
        ];
    }
}
