<?php

namespace App\Http\Middleware;

use App\Models\Platform\Tenant;
use App\Services\Settings\TenantSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStorefrontEnabled
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenancy()->tenant;

        if ($tenant instanceof Tenant && ! $tenant->storefront_enabled) {
            // Redirect to external website if set
            if ($tenant->external_website) {
                return redirect()->away($tenant->external_website);
            }

            $settings = resolve(TenantSettings::class);

            // Otherwise show disabled page
            return response()->view('platform.storefront-disabled', [
                'storeName' => $settings->store->name,
                'tenant' => $tenant,
            ]);
        }

        return $next($request);
    }
}
