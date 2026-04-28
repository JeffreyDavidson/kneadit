<?php

namespace App\Http\Middleware;

use App\Models\Platform\Tenant;
use App\Services\Tenants\TenantSQLiteDatabaseManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException;
use Stancl\Tenancy\Exceptions\TenantDatabaseDoesNotExistException;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyIfNeeded
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if tenancy is already initialized
        if (tenant()) {
            return $next($request);
        }

        // Redirect www to apex domain
        $host = $request->getHost();
        if ($host === 'www.getkneadit.app') {
            return redirect()->to(
                $request->getScheme() . '://getkneadit.app' . $request->getRequestUri(),
                301,
            );
        }

        // Skip if we're on a central domain
        if (in_array($host, config('tenancy.central_domains', []), true)) {
            return $next($request);
        }

        // We're on a subdomain — initialize tenancy
        try {
            return resolve(InitializeTenancyByDomainOrSubdomain::class)->handle($request, $next);
        } catch (TenantCouldNotBeIdentifiedOnDomainException) {
            Log::warning('Tenant not found for domain', ['domain' => $host]);
            abort(404, 'Bakery not found.');
        } catch (TenantDatabaseDoesNotExistException $exception) {
            return $this->handleOrphanTenantRow($request, $next, $exception);
        }
    }

    /**
     * Central tenant row exists but its SQLite file is missing — typical of
     * dev workflows that wipe gitignored database/ contents while leaving
     * central rows intact. Auto-heals in local environments so work isn't
     * blocked; returns 503 in production so the operator can surface and
     * fix via `tenants:doctor --fix`.
     */
    private function handleOrphanTenantRow(Request $request, Closure $next, TenantDatabaseDoesNotExistException $exception): Response
    {
        $tenantId = $this->extractTenantId($request);

        Log::error('Orphan tenant: central row exists but SQLite database is missing', [
            'tenant_id' => $tenantId,
            'domain' => $request->getHost(),
            'message' => $exception->getMessage(),
        ]);

        if (app()->isLocal() && $tenantId !== null) {
            $tenant = Tenant::query()->find($tenantId);

            if ($tenant && $this->recreateMissingDatabase($tenant)) {
                Log::info('Auto-recreated orphan tenant database', ['tenant_id' => $tenantId]);

                return resolve(InitializeTenancyByDomainOrSubdomain::class)->handle($request, $next);
            }
        }

        abort(503, 'Bakery temporarily unavailable. Run `php artisan tenants:doctor --fix` to repair.');
    }

    private function extractTenantId(Request $request): ?string
    {
        $parts = explode('.', $request->getHost());

        return $parts[0] ?? null;
    }

    private function recreateMissingDatabase(Tenant $tenant): bool
    {
        try {
            // The failed init left tenancy in a partial state — central
            // connection points at the missing tenant DB. Reset before
            // calling tenants:migrate so the migration runs against a
            // clean central connection and properly switches to the
            // freshly-created tenant DB.
            tenancy()->end();

            resolve(TenantSQLiteDatabaseManager::class)->createDatabase($tenant);
            Artisan::call('tenants:migrate', [
                '--tenants' => [$tenant->id],
                '--force' => true,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Auto-recovery failed for orphan tenant', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
