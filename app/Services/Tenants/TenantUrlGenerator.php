<?php

namespace App\Services\Tenants;

use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Support\Uri;

final class TenantUrlGenerator
{
    public function storefront(Tenant $tenant): string
    {
        return Str::rtrim((string) $this->tenantUri($tenant), '/');
    }

    public function storefrontHost(Tenant $tenant): string
    {
        return (string) $this->tenantUri($tenant)->authority();
    }

    public function admin(Tenant $tenant): string
    {
        return (string) $this->tenantUri($tenant)->withPath('/admin');
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

        return $uri
            ->withScheme($uri->scheme() ?: 'https')
            ->withHost("{$tenant->id}.{$host}")
            ->withPath('/')
            ->replaceQuery([])
            ->withoutFragment();
    }
}
