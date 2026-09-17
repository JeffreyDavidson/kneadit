<?php

declare(strict_types=1);

namespace App\Actions\Platform;

use App\Models\Platform\Tenant;
use App\Services\Platform\ForgeService;
use Stancl\Tenancy\Database\Models\Domain;

class AddCustomDomain
{
    public function __construct(
        private readonly ForgeService $forge,
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
