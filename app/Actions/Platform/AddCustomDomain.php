<?php

declare(strict_types=1);

namespace App\Actions\Platform;

use App\Models\Platform\Tenant;
use App\Services\Platform\CustomDomainService;
use App\Services\Platform\ForgeService;
use Illuminate\Validation\ValidationException;
use Stancl\Tenancy\Database\Models\Domain;

class AddCustomDomain
{
    public function __construct(
        private readonly ForgeService $forge,
        private readonly CustomDomainService $domains,
        private readonly RemoveCustomDomain $removeCustomDomain,
    ) {}

    /**
     * Normalizes and claims a custom domain for the bakery, returning the saved hostname.
     *
     * @throws ValidationException When the domain is malformed, reserved by the platform or owned by another bakery.
     */
    public function __invoke(Tenant $tenant, string $domain): string
    {
        $domain = $this->domains->normalize($domain);

        $this->ensureAvailable($tenant, $domain);

        if ($tenant->custom_domain === $domain) {
            return $domain;
        }

        // Replacing a domain: release the old one (domain record and Forge alias) first.
        if ($tenant->custom_domain) {
            ($this->removeCustomDomain)($tenant);
        }

        $tenant->update(['custom_domain' => $domain]);

        if (! Domain::query()->where('domain', $domain)->exists()) {
            $tenant->createDomain(['domain' => $domain]);
        }

        if (ForgeService::isConfigured()) {
            $this->forge->addDomainAlias($domain);
        }

        return $domain;
    }

    private function ensureAvailable(Tenant $tenant, string $domain): void
    {
        if (! $this->domains->isValidFormat($domain)) {
            throw $this->rejection('Enter a valid domain name, such as sweetdreamsbakery.com.');
        }

        if ($this->domains->isPlatformDomain($domain)) {
            throw $this->rejection('That domain is reserved by the platform. Use a domain you own.');
        }

        $takenByAnotherBakery = Domain::query()
            ->where('domain', $domain)
            ->where('tenant_id', '!=', $tenant->id)
            ->exists();

        if ($takenByAnotherBakery) {
            throw $this->rejection('That domain is already in use by another bakery.');
        }
    }

    private function rejection(string $message): ValidationException
    {
        return ValidationException::withMessages(['custom_domain' => $message]);
    }
}
