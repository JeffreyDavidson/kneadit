<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Tenant;

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
        Setting::flushCache();

        try {
            return $callback($tenant);
        } finally {
            tenancy()->end();
        }
    }
}
