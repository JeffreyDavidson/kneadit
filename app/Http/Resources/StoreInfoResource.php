<?php

namespace App\Http\Resources;

use App\Services\Settings\TenantSettings;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TenantSettings */
class StoreInfoResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'store_name' => $this->resource->storeName,
            'tagline' => $this->resource->storeTagline ?? '',
            'phone' => $this->resource->storePhone ?? '',
            'email' => $this->resource->storeEmail ?? '',
            'address' => $this->resource->storeAddress ?? '',
            'logo_url' => $this->resource->storeLogoUrl() ?? '',
            'colors' => [
                'primary' => $this->resource->brandColorPrimary,
            ],
            'hours' => $this->resource->operatingHours,
            'social_links' => $this->resource->socialMediaLinks,
        ];
    }
}
