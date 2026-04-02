<?php

namespace App\Services\Tenant;

use App\Models\Platform\Tenant;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettingsRegistry;
use Illuminate\Support\Facades\Log;

class TenancyManager
{
    /**
     * Execute a callback within a tenant's context, ensuring proper cleanup.
     *
     * @template TReturn
     *
     * @param callable(Tenant): TReturn $callback
     * @return TReturn
     */
    public function withinTenant(Tenant $tenant, callable $callback): mixed
    {
        tenancy()->initialize($tenant);
        resolve(SettingsManager::class)->flushCache();
        resolve(TenantSettingsRegistry::class)->flush();

        try {
            return $callback($tenant);
        } finally {
            tenancy()->end();
        }
    }

    /**
     * Iterate all tenants, executing a callback within each tenant's context.
     *
     * Returns the number of tenants that failed. The callback receives
     * the Tenant and its TenantSettings (already resolved within context).
     *
     * @param callable(Tenant, TenantSettings): void $callback
     * @param callable(Tenant, \Throwable): void|null $onError
     */
    public function forEachTenant(callable $callback, ?callable $onError = null): int
    {
        $failures = 0;

        foreach (Tenant::query()->cursor() as $tenant) {
            try {
                $this->withinTenant($tenant, function () use ($tenant, $callback) {
                    $settings = app(TenantSettings::class);
                    $callback($tenant, $settings);
                });
            } catch (\Throwable $e) {
                $failures++;

                if ($onError) {
                    $onError($tenant, $e);
                } else {
                    Log::warning("Tenant processing failed for {$tenant->id}", [
                        'tenant' => $tenant->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $failures;
    }
}
