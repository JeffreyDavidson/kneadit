<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Storefront\HomeController;
use App\Models\Platform\Tenant;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use RuntimeException;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Symfony\Component\HttpFoundation\Response;

class RootController extends Controller
{
    public function __invoke(): View|Response
    {
        $centralDomains = Config::array('tenancy.central_domains', []);
        $centralDomains = array_values(array_filter($centralDomains, is_string(...)));

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

        $response = $middleware->handle(request(), function (Request $request): View|Response {
            $tenant = tenant();

            if (! $tenant instanceof Tenant) {
                return view('platform.welcome');
            }

            // If storefront is disabled and they have an external website, redirect there
            $externalUrl = $tenant->external_website;
            if (! $tenant->storefront_enabled && $externalUrl && (Str::startsWith($externalUrl, 'https://') || Str::startsWith($externalUrl, 'http://')) && filter_var($externalUrl, FILTER_VALIDATE_URL)) {
                return redirect()->away($externalUrl);
            }

            // If storefront is disabled but no external URL, show a minimal page
            if (! $tenant->storefront_enabled) {
                $settings = resolve(TenantSettings::class);

                return response()->view('platform.storefront-disabled', [
                    'storeName' => $settings->store->name,
                    'tenant' => $tenant,
                ]);
            }

            return resolve(HomeController::class)(resolve(TenantSettings::class));
        });

        if ($response instanceof View || $response instanceof Response) {
            return $response;
        }

        throw new RuntimeException('Root tenant middleware returned an unsupported response.');
    }
}
