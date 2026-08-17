<?php

namespace App\Actions\Tenants;

use App\Events\Platform\TenantOnboarded;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;

class CompleteTenantOnboarding
{
    public function __construct(
        private readonly CreateTenant $createTenant,
        private readonly CompleteReferral $completeReferral,
    ) {}

    public function __invoke(
        User $user,
        string $storeName,
        string $subdomain,
        bool $useKneadItStorefront,
        ?string $externalWebsite,
        ?string $referralCode,
        string $adminUrl,
    ): Tenant {
        $tenant = ($this->createTenant)(
            $user,
            $storeName,
            $subdomain,
            $useKneadItStorefront,
            $externalWebsite,
        );

        ($this->completeReferral)($referralCode, (string) $tenant->id, $user->email);

        event(new TenantOnboarded($user, $tenant, $adminUrl));

        return $tenant;
    }
}
