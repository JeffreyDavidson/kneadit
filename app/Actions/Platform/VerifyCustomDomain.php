<?php

declare(strict_types=1);

namespace App\Actions\Platform;

use App\Enums\Platform\DomainCheck;
use App\Models\Platform\Tenant;
use App\Services\Platform\CustomDomainService;

class VerifyCustomDomain
{
    public function __construct(
        private readonly CustomDomainService $domains,
    ) {}

    /**
     * Checks the bakery's custom domain and records the outcome: the verification
     * time is set when the domain reaches the server (directly, or through a proxy that
     * passes the ownership proof) over HTTPS, and cleared when the check fails (or when the bakery has no custom domain).
     * Links only use the custom domain while it is verified.
     *
     * A domain that is already verified keeps its original verification time.
     */
    public function __invoke(Tenant $tenant): DomainCheck
    {
        $domain = $tenant->custom_domain;

        $check = is_string($domain) && $domain !== ''
            ? $this->domains->verify($domain)
            : DomainCheck::DnsMissing;

        if (! $check->isVerified()) {
            if ($tenant->custom_domain_verified_at !== null) {
                $tenant->update(['custom_domain_verified_at' => null]);
            }

            return $check;
        }

        if ($tenant->custom_domain_verified_at === null) {
            $tenant->update(['custom_domain_verified_at' => now()]);
        }

        return $check;
    }
}
