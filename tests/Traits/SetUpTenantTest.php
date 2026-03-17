<?php

namespace Tests\Traits;

use App\Http\Middleware\EnsureStorefrontEnabled;
use App\Http\Middleware\TrackPageView;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

trait SetUpTenantTest
{
    /**
     * Middleware classes to bypass during tenant tests.
     *
     * @var array<int, class-string>
     */
    protected array $tenantMiddleware = [
        InitializeTenancyByDomainOrSubdomain::class,
        PreventAccessFromCentralDomains::class,
        EnsureStorefrontEnabled::class,
        TrackPageView::class,
    ];

    /**
     * Set up the tenant testing environment.
     *
     * Configures the central database connection, clears central domains,
     * and runs tenant migrations so tests can interact with tenant tables.
     */
    protected function setUpTenant(): void
    {
        config(['database.connections.central' => config('database.connections.sqlite')]);
        config(['tenancy.central_domains' => []]);

        $tenantMigrationPath = database_path('migrations/tenant');

        if (is_dir($tenantMigrationPath)) {
            $this->artisan('migrate', [
                '--path' => $tenantMigrationPath,
                '--realpath' => true,
            ]);
        }
    }
}
