<?php

namespace App\Actions\Platform;

use App\Models\Platform\Tenant;
use App\Services\Platform\ForgeService;
use Stancl\Tenancy\Database\Models\Domain;

class AddCustomDomain
{
    public function __construct(
        private ForgeService $forge,
    ) {}

    public function __invoke(Tenant $tenant, string $domain): void
    {
        $tenant->update(['custom_domain' => $domain]);

        if (! Domain::query()->where('domain', $domain)->exists()) {
            $tenant->createDomain(['domain' => $domain]);
        }

        if (ForgeService::isConfigured()) {
            $this->forge->addDomainAlias($domain);
        }
    }
}
