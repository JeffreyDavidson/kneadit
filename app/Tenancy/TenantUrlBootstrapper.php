<?php

declare(strict_types=1);

namespace App\Tenancy;

use App\Models\Platform\Tenant as BakeryTenant;
use App\Services\Tenants\TenantUrlGenerator;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Uri;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

/**
 * Points generated URLs at the bakery's storefront while tenancy is active.
 *
 * On a request to the bakery's own host the URL generator already uses that
 * host, so nothing changes there. Work that enters a bakery from elsewhere
 * (the scheduler, queue workers, central panel actions) would otherwise build
 * every route() and url() against the platform domain, where bakery routes do
 * not exist. That broke the links in queued and scheduled customer emails.
 */
final class TenantUrlBootstrapper implements TenancyBootstrapper
{
    private bool $overridden = false;

    public function __construct(
        private readonly UrlGenerator $url,
        private readonly TenantUrlGenerator $tenantUrls,
    ) {}

    public function bootstrap(Tenant $tenant): void
    {
        if (! $tenant instanceof BakeryTenant) {
            return;
        }

        if ($this->isOnBakeryHost()) {
            return;
        }

        $root = $this->tenantUrls->primaryStorefront($tenant);

        $this->url->forceRootUrl($root);
        $this->url->forceScheme(Uri::of($root)->scheme());
        $this->overridden = true;
    }

    public function revert(): void
    {
        if (! $this->overridden) {
            return;
        }

        $this->url->forceRootUrl(null);
        $this->url->forceScheme(null);
        $this->overridden = false;
    }

    /**
     * Whether the request being served is on a host other than a central one,
     * which means a bakery's host (subdomain or custom domain) identified it.
     */
    private function isOnBakeryHost(): bool
    {
        return ! in_array($this->url->getRequest()->getHost(), Config::array('tenancy.central_domains'), true);
    }
}
