<?php

namespace App\Http\Controllers;

use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;

class RootController extends Controller
{
    public function index()
    {
        $centralDomains = config('tenancy.central_domains', []);

        if (in_array(request()->getHost(), $centralDomains)) {
            return view('welcome');
        }

        // Tenant subdomain — initialize tenancy and serve storefront or redirect
        $middleware = app(InitializeTenancyByDomainOrSubdomain::class);

        return $middleware->handle(request(), function ($request) {
            $tenant = tenant();

            // If storefront is disabled and they have an external website, redirect there
            if ($tenant && ! $tenant->storefront_enabled && $tenant->external_website) {
                return redirect()->away($tenant->external_website);
            }

            // If storefront is disabled but no external URL, show a minimal page
            if ($tenant && ! $tenant->storefront_enabled) {
                return response()->view('storefront-disabled', [
                    'storeName' => \App\Models\Setting::get('store_name', $tenant->store_name ?? 'Our Bakery'),
                    'tenant' => $tenant,
                ]);
            }

            return app(\App\Http\Controllers\StorefrontController::class)->home();
        });
    }
}
