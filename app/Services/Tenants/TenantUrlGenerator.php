<?php

namespace App\Services\Tenants;

use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Support\Uri;
use Stancl\Tenancy\Database\Models\Domain;

final class TenantUrlGenerator
{
    public function storefront(Tenant $tenant): string
    {
        return Str::rtrim((string) $this->tenantUri($tenant), '/');
    }

    /**
     * The URL customers should be sent to: the bakery's custom domain when it
     * has one that routes to it, otherwise its subdomain storefront.
     *
     * A custom domain only counts when its DNS has been verified as pointing at
     * the server (`custom_domain_verified_at`), so links never use a domain that
     * is saved but not yet set up, and when a `domains` row ties it to this
     * tenant, because that row is what makes the host reach the bakery.
     */
    public function primaryStorefront(Tenant $tenant): string
    {
        $customDomain = $tenant->custom_domain;

        if (! is_string($customDomain) || $customDomain === '' || $tenant->custom_domain_verified_at === null) {
            return $this->storefront($tenant);
        }

        if (! Domain::query()->where('domain', $customDomain)->where('tenant_id', $tenant->id)->exists()) {
            return $this->storefront($tenant);
        }

        return Str::rtrim((string) $this->tenantUri($tenant)->withHost($customDomain)->withPort(null), '/');
    }

    public function storefrontHost(Tenant $tenant): string
    {
        return (string) $this->tenantUri($tenant)->authority();
    }

    public function admin(Tenant $tenant): string
    {
        return (string) $this->tenantUri($tenant)->withPath('/admin');
    }

    public function helpCenter(Tenant $tenant): string
    {
        return (string) $this->tenantUri($tenant)
            ->withPath(URL::route('filament.admin.pages.help-center', absolute: false));
    }

    public function impersonation(Tenant $tenant, string $token): string
    {
        return (string) $this->tenantUri($tenant)
            ->withPath(URL::route('impersonate.consume', ['token' => $token], absolute: false));
    }

    private function tenantUri(Tenant $tenant): Uri
    {
        $uri = Uri::of(Config::string('app.url'));
        $host = $uri->host();

        if ($host === null || $host === '') {
            throw new \UnexpectedValueException('The application URL must contain a host.');
        }

        $configuredTenantDomain = Config::get('tenancy.tenant_domain');
        $tenantDomain = is_string($configuredTenantDomain) && $configuredTenantDomain !== ''
            ? $configuredTenantDomain
            : $host;

        return $uri
            ->withScheme($uri->scheme() ?: 'https')
            ->withHost("{$tenant->id}.{$tenantDomain}")
            ->withPath('/')
            ->replaceQuery([])
            ->withoutFragment();
    }
}
