<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureStorefrontEnabled;
use App\Http\Middleware\TrackPageView;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| These routes are for individual tenant storefronts and admin panels.
| Each baker gets their own subdomain: bakery-name.getkneadit.app
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomainOrSubdomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    // Note: The "/" route is handled by RootController in web.php
    // to avoid overriding the central domain landing page.

    // PWA, invitations, impersonation, driver, and integration routes remain
    // outside the storefront-enabled check.
    require __DIR__ . '/tenant/access.php';
    require __DIR__ . '/tenant/admin.php';

    // Storefront routes — only accessible when storefront is enabled
    // When disabled, these redirect to the external website or show a minimal page
    Route::middleware([EnsureStorefrontEnabled::class, TrackPageView::class])->group(function () {
        require __DIR__ . '/tenant/storefront.php';
        require __DIR__ . '/tenant/account.php';
        require __DIR__ . '/tenant/orders.php';
    });

    // Tenant Storefront API (JSON, no CSRF)
    Route::prefix('api')
        ->withoutMiddleware(PreventRequestForgery::class)
        ->group(function () {
            require __DIR__ . '/tenant/api.php';
        });
});
