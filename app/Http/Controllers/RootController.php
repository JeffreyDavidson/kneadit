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

        // Tenant subdomain — initialize tenancy and serve storefront
        $middleware = app(InitializeTenancyByDomainOrSubdomain::class);

        return $middleware->handle(request(), function ($request) {
            return app(\App\Http\Controllers\StorefrontController::class)->home();
        });
    }
}
