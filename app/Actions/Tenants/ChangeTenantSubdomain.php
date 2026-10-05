<?php

declare(strict_types=1);

namespace App\Actions\Tenants;

use App\Http\Requests\Storefront\StoreOnboardingRequest;
use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Stancl\Tenancy\Database\Models\Domain;

/**
 * Moves a bakery to a new `{subdomain}.{tenant domain}` address without touching its id
 * (the primary key, the tenant database file name, storage paths and foreign keys).
 *
 * The old dot-less `domains` row is kept, so old links still resolve (and are redirected
 * to the current subdomain by the tenancy middleware).
 */
class ChangeTenantSubdomain
{
    /**
     * @throws ValidationException When the subdomain breaks the onboarding rules or is already taken.
     */
    public function __invoke(Tenant $tenant, string $subdomain): string
    {
        $subdomain = Str::lower(trim($subdomain));

        if ($tenant->subdomain === $subdomain) {
            return $subdomain;
        }

        Validator::make(
            ['subdomain' => $subdomain],
            ['subdomain' => StoreOnboardingRequest::subdomainRules($tenant)],
            StoreOnboardingRequest::subdomainMessages(),
        )->validate();

        DB::transaction(function () use ($tenant, $subdomain): void {
            Domain::query()->firstOrCreate(
                ['domain' => $subdomain],
                ['tenant_id' => $tenant->id],
            );

            $tenant->update(['subdomain' => $subdomain]);
        });

        return $subdomain;
    }
}
