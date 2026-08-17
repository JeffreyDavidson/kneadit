<?php

namespace App\Tenancy;

use InvalidArgumentException;
use Stancl\Tenancy\Bootstrappers\FilesystemTenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

class TenantFilesystemBootstrapper extends FilesystemTenancyBootstrapper
{
    public function bootstrap(Tenant $tenant): void
    {
        $tenantKey = $tenant->getTenantKey();

        if ((! is_int($tenantKey) && ! is_string($tenantKey))
            || preg_match('/\A[A-Za-z0-9][A-Za-z0-9_-]*\z/D', (string) $tenantKey) !== 1) {
            throw new InvalidArgumentException('Tenant identifier contains unsafe path characters.');
        }

        parent::bootstrap($tenant);
    }
}
