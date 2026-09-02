<?php

namespace App\Services\Tenants;

use App\Models\Platform\Tenant;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettings;
use App\Services\Settings\TenantSettingsRegistry;
use Illuminate\Support\Facades\Log;

class TenancyManager
{
    public function __construct(
        private SettingsManager $settingsManager,
        private TenantSettingsRegistry $tenantSettingsRegistry,
    ) {}

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
        $this->settingsManager->flushCache();
        $this->tenantSettingsRegistry->flush();

        try {
            return $callback($tenant);
        } finally {
            tenancy()->end();
            $this->settingsManager->flushCache();
            $this->tenantSettingsRegistry->flush();
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
                    $settings = $this->tenantSettingsRegistry->all();
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
