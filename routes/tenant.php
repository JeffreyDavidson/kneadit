<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AcceptInvitationController;
use App\Http\Controllers\Auth\ShowInvitationController;
use App\Http\Controllers\Central\ConsumeImpersonationController;
use App\Http\Controllers\Storefront\AppIconController;
use App\Http\Controllers\Storefront\DriverDashboardController;
use App\Http\Controllers\Storefront\ManifestController;
use App\Http\Controllers\Storefront\MarkOrderDeliveredController;
use App\Http\Controllers\Stripe\StripeConnectController;
use App\Http\Middleware\EnsureStorefrontEnabled;
use App\Http\Middleware\ResolveInvitation;
use App\Http\Middleware\TrackPageView;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
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

    // PWA routes (outside storefront-enabled check so manifest/SW always work)
    Route::get('manifest.json', ManifestController::class)->name('manifest');
    // Service worker removed — was caching stale pages after deploys
    Route::get('icons/icon-{size}.png', AppIconController::class)->name('app.icon');

    // Impersonation token consumer (from central admin)
    Route::get('impersonate/{token}', ConsumeImpersonationController::class)
        ->name('impersonate.consume');

    // Stripe Connect OAuth
    Route::get('stripe/connect', StripeConnectController::class)
        ->middleware('auth')
        ->name('stripe.connect');

    // Driver view (no auth, shared via link)
    Route::prefix('driver')->name('driver.')->group(function () {
        Route::get('/', DriverDashboardController::class)->name('index');
        Route::post('{order}/delivered', MarkOrderDeliveredController::class)->name('delivered')->middleware('auth');
    });

    // Staff invitation routes (outside auth & storefront middleware)
    Route::get('invite/{token}', ShowInvitationController::class)->name('invitation.show')->middleware(ResolveInvitation::class);
    Route::post('invite/{token}', AcceptInvitationController::class)->name('invitation.accept')->middleware([ResolveInvitation::class, 'throttle:5,1']);

    // Storefront routes — only accessible when storefront is enabled
    // When disabled, these redirect to the external website or show a minimal page
    Route::middleware([EnsureStorefrontEnabled::class, TrackPageView::class])->group(function () {
        require __DIR__ . '/tenant/storefront.php';
        require __DIR__ . '/tenant/orders.php';
    });

    // Tenant Storefront API (JSON, no CSRF)
    Route::prefix('api')
        ->withoutMiddleware(VerifyCsrfToken::class)
        ->group(function () {
            require __DIR__ . '/tenant/api.php';
        });
});
