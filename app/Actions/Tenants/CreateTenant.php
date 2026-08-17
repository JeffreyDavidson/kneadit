<?php

namespace App\Actions\Tenants;

use App\Models\Platform\Tenant;
use App\Models\Staff\User;

class CreateTenant
{
    public function __construct(
        private readonly CreateTenantRecord $createTenantRecord,
        private readonly ProvisionTenantOwner $provisionTenantOwner,
    ) {}

    public function __invoke(
        User $user,
        string $storeName,
        string $subdomain,
        bool $useKneadItStorefront,
        ?string $externalWebsite = null,
    ): Tenant {
        $tenant = ($this->createTenantRecord)(
            $user,
            $storeName,
            $subdomain,
            $useKneadItStorefront,
            $externalWebsite,
        );

        // Tenant provisioning uses a separate SQLite connection. Delete the
        // central tenant if provisioning fails so callers see one outcome.
        try {
            ($this->provisionTenantOwner)(
                $tenant,
                $user,
                $storeName,
                $useKneadItStorefront,
                $externalWebsite,
            );
        } catch (\Throwable $exception) {
            $tenant->delete();

            throw $exception;
        }

        return $tenant;
    }
}
