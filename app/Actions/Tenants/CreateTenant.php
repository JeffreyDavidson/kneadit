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

        // The central tenant + domain rows are committed above. The tenant DB
        // is created+migrated synchronously by Stancl's TenantCreated job
        // pipeline. Now we seed the tenant DB. If THAT fails — and SQLite is
        // a separate connection from central, so it's outside the central
        // transaction — we'd otherwise leave an orphan central tenant row
        // (the situation tenants:doctor was built to recover from).
        //
        // Catch + cascade-delete the central tenant on failure so the caller
        // sees an all-or-nothing outcome. $tenant->delete() cascades to the
        // domain row (FK onDelete cascade) and fires Stancl's TenantDeleted
        // event which runs DeleteDatabase to remove the SQLite file.
        try {
            ($this->provisionTenantOwner)(
                $tenant,
                $user,
                $storeName,
                $useKneadItStorefront,
                $externalWebsite,
            );
        } catch (\Throwable $e) {
            $tenant->delete();
            throw $e;
        }

        return $tenant;
    }
}
