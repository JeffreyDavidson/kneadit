<?php

namespace App\Services\Tenant;

use App\Models\Tenant;
use App\Services\Settings\SettingsManager;

class TenancyManager
{
    /**
     * Execute a callback within a tenant's context, ensuring proper cleanup.
     *
     * @template TReturn
     *
     * @param  callable(Tenant): TReturn  $callback
     * @return TReturn
     */
    public function withinTenant(Tenant $tenant, callable $callback): mixed
    {
        tenancy()->initialize($tenant);
        resolve(SettingsManager::class)->flushCache();

        try {
            return $callback($tenant);
        } finally {
            tenancy()->end();
        }
    }
}
