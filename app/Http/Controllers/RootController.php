<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Storefront\HomeController;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Symfony\Component\HttpFoundation\Response;

class RootController extends Controller
{
    public function index(): View|Response
    {
        $centralDomains = config('tenancy.central_domains', []);

        if (in_array(request()->getHost(), $centralDomains)) {
            return view('welcome');
        }

        // Tenant subdomain — initialize tenancy and serve storefront or redirect
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
                return response()->view('storefront-disabled', [
                    'storeName' => settings('store_name', $tenant->store_name ?? 'Our Bakery'),
                    'tenant' => $tenant,
                ]);
            }

            return resolve(HomeController::class)();
        });
    }
}
