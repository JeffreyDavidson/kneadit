<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Storefront\HomeController;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Symfony\Component\HttpFoundation\Response;

class RootController extends Controller
{
    public function __invoke(): View|Response
    {
        $centralDomains = config('tenancy.central_domains', []);

        if (in_array(request()->getHost(), $centralDomains)) {
            return view('platform.welcome');
        }

        // Tech debt: This manually invokes InitializeTenancyByDomainOrSubdomain middleware
        // instead of relying on route-level middleware. Ideally, the root "/" route should be
        // split into a central-only route and a tenant-only route, each with proper middleware
        // applied in the route file. This would eliminate the manual resolve() call and the
        // nested closure. Skipped for now because it requires reworking how the root domain
        // dispatches between central and tenant contexts across the entire route configuration.
        $middleware = resolve(InitializeTenancyByDomainOrSubdomain::class);

        return $middleware->handle(request(), function (Request $request) {
            $tenant = tenant();

            // If storefront is disabled and they have an external website, redirect there
            $externalUrl = $tenant?->external_website;
            if ($tenant && ! $tenant->storefront_enabled && $externalUrl && (Str::startsWith($externalUrl, 'https://') || Str::startsWith($externalUrl, 'http://')) && filter_var($externalUrl, FILTER_VALIDATE_URL)) {
                return redirect()->away($externalUrl);
            }

            // If storefront is disabled but no external URL, show a minimal page
            if ($tenant && ! $tenant->storefront_enabled) {
                $settings = app(TenantSettings::class);

                return response()->view('platform.storefront-disabled', [
                    'storeName' => $settings->store->name,
                    'tenant' => $tenant,
                ]);
            }

            return resolve(HomeController::class)(app(TenantSettings::class));
        });
    }
}
