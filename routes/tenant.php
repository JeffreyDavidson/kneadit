<?php

declare(strict_types=1);

use App\Http\Controllers\Api\StorefrontApiController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\StorefrontController;
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
    Route::get('/manifest.json', [StorefrontController::class, 'manifest'])->name('manifest');
    Route::get('/service-worker.js', function () {
        return response()->file(public_path('service-worker.js'), ['Content-Type' => 'application/javascript']);
    });
    Route::get('/icons/icon-{size}.png', [StorefrontController::class, 'appIcon'])->name('app.icon');

    // Storefront routes — only accessible when storefront is enabled
    // When disabled, these redirect to the external website or show a minimal page
    Route::middleware(\App\Http\Middleware\EnsureStorefrontEnabled::class)->group(function () {
        Route::get('/menu', [StorefrontController::class, 'menu'])->name('storefront.menu');
        Route::get('/order', [OrderController::class, 'index'])->name('order.create');
        Route::post('/order', [OrderController::class, 'store'])->name('order.store');
        Route::get('/order/confirmation/{order}', [OrderController::class, 'confirmation'])->name('order.confirmation');
        Route::get('/about', [StorefrontController::class, 'about'])->name('storefront.about');
        Route::get('/reviews', [StorefrontController::class, 'reviews'])->name('storefront.reviews');
        Route::get('/track', [OrderController::class, 'track'])->name('order.track');
        Route::post('/track', [OrderController::class, 'trackLookup'])->name('order.track.lookup');
        Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
        Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

        // Capacity check (AJAX)
        Route::get('/capacity/check/{date}', [OrderController::class, 'checkCapacity'])->name('capacity.check');

        // Coupon validation (AJAX)
        Route::post('/coupon/apply', [OrderController::class, 'applyCoupon'])->name('coupon.apply');

        // Customer favorites (AJAX)
        Route::get('/favorites', [StorefrontController::class, 'getFavorites'])->name('favorites.get');
        Route::post('/favorites/toggle', [StorefrontController::class, 'toggleFavorite'])->name('favorites.toggle');
    });

    // Tenant Storefront API (JSON, no CSRF)
    Route::prefix('api')
        ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
        ->group(function () {
            Route::get('/store', [StorefrontApiController::class, 'store']);
            Route::get('/categories', [StorefrontApiController::class, 'categories']);
            Route::get('/products', [StorefrontApiController::class, 'products']);
            Route::get('/menu', [StorefrontApiController::class, 'menu']);
            Route::get('/capacity/{date}', [StorefrontApiController::class, 'capacity']);
            Route::get('/reviews', [StorefrontApiController::class, 'reviews']);
            Route::get('/gallery', [StorefrontApiController::class, 'gallery']);
            Route::get('/favorites', [StorefrontApiController::class, 'favorites']);

            Route::post('/orders', [StorefrontApiController::class, 'submitOrder']);
            Route::post('/coupon/validate', [StorefrontApiController::class, 'validateCoupon']);
            Route::post('/reviews', [StorefrontApiController::class, 'submitReview']);
            Route::post('/contact', [StorefrontApiController::class, 'submitContact']);
            Route::post('/favorites/toggle', [StorefrontApiController::class, 'toggleFavorite']);
            Route::post('/waitlist', [StorefrontApiController::class, 'waitlist']);
        });
});

