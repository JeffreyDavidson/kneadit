<?php

declare(strict_types=1);

namespace App\Actions\Platform;

use App\Models\Platform\Tenant;
use App\Services\Platform\CustomDomainService;

class VerifyCustomDomain
{
    public function __construct(
        private readonly CustomDomainService $domains,
    ) {}

    /**
     * Checks the bakery's custom domain against DNS and records the outcome: the
     * verification time is set when the domain points at the server and cleared
     * when it does not (or when the bakery has no custom domain). Links only use
     * the custom domain while it is verified.
     *
     * A domain that is already verified keeps its original verification time.
     *
     * @return bool Whether the domain is verified after the check.
     */
    public function __invoke(Tenant $tenant): bool
    {
        $domain = $tenant->custom_domain;

        if (! is_string($domain) || $domain === '' || ! $this->domains->isDnsVerified($domain)) {
            if ($tenant->custom_domain_verified_at !== null) {
                $tenant->update(['custom_domain_verified_at' => null]);
            }

            return false;
        }

        if ($tenant->custom_domain_verified_at === null) {
            $tenant->update(['custom_domain_verified_at' => now()]);
        }

        return true;
    }
}
