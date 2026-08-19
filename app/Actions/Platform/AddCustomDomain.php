<?php

namespace App\Actions\Platform;

use App\Models\Platform\Tenant;
use App\Services\Platform\ForgeService;
use Stancl\Tenancy\Database\Models\Domain;

class AddCustomDomain
{
    public function __invoke(Tenant $tenant, string $domain): void
    {
        $tenant->update(['custom_domain' => $domain]);

        if (! Domain::query()->where('domain', $domain)->exists()) {
            Domain::query()->create(['domain' => $domain, 'tenant_id' => $tenant->id]);
        }

        if (ForgeService::isConfigured()) {
            resolve(ForgeService::class)->addDomainAlias($domain);
        }
    }
}
