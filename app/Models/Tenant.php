<?php

namespace App\Models;

use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    /**
     * Custom columns on the tenants table.
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'email',
            'plan',
            'trial_ends_at',
            'store_name',
            'store_logo',
            'brand_color_primary',
            'brand_color_secondary',
            'storefront_enabled',
            'external_website',
            'is_active',
        ];
    }

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'storefront_enabled' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
