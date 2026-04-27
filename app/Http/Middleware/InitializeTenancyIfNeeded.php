<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
            // Central tenant row exists but its SQLite file is missing —
            // typically caused by dev workflows that wipe gitignored database/
            // contents (git clean -fdx, manual cleanup, restore from backup)
            // without also clearing the central rows. Auto-recovery from
            // middleware is risky (tenancy state is mid-init), so we just log
            // loudly + 503. Operator fixes with `php artisan tenants:doctor --fix`.
            Log::error('Orphan tenant: central row exists but SQLite database is missing', [
                'tenant_id' => $this->extractTenantId($request),
                'domain' => $host,
                'message' => $exception->getMessage(),
            ]);
            abort(503, 'Bakery temporarily unavailable. Run `php artisan tenants:doctor --fix` to repair.');
        }
    }

    private function extractTenantId(Request $request): ?string
    {
        $parts = explode('.', $request->getHost());

        return $parts[0] ?? null;
    }
}
