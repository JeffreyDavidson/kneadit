<?php

namespace App\Actions\Platform;

use App\Models\Platform\Tenant;
use App\Services\Platform\ForgeService;
use Stancl\Tenancy\Database\Models\Domain;

class RemoveCustomDomain
{
    public function __invoke(Tenant $tenant): void
    {
        $oldDomain = $tenant->custom_domain;

        if ($oldDomain) {
            Domain::query()->where('domain', $oldDomain)->where('tenant_id', $tenant->id)->delete();

            if (ForgeService::isConfigured()) {
                resolve(ForgeService::class)->removeDomainAlias($oldDomain);
            }
        }

        $tenant->update(['custom_domain' => null]);
    }
}
