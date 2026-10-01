<?php

namespace App\Services\Customers;

use App\Models\Customers\Customer;
use App\Models\Platform\Tenant;
use App\Services\Tenants\TenantUrlGenerator;
use Illuminate\Support\Facades\URL;

/**
 * Builds the link a marketing email uses to let a customer opt out.
 *
 * The URL never expires (old emails must keep working) and is signed against
 * its path only. That keeps the signature valid on whichever host serves it,
 * so mail queued from a scheduler or worker, where the request host is not the
 * bakery's, still links to the bakery's storefront.
 */
class MarketingUnsubscribeLinks
{
    public function __construct(
        private readonly TenantUrlGenerator $tenantUrls,
    ) {}

    public function unsubscribe(Customer $customer): string
    {
        $path = URL::signedRoute('emailUnsubscribe.show', ['customer' => $customer->getKey()], absolute: false);
        $tenant = tenancy()->tenant;

        if (! $tenant instanceof Tenant) {
            return URL::to($path);
        }

        return "{$this->tenantUrls->primaryStorefront($tenant)}{$path}";
    }
}
