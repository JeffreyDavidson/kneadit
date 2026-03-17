<?php

declare(strict_types=1);

use App\Http\Controllers\Api\StorefrontApiController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\ImpersonateController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Storefront\CateringController;
use App\Http\Controllers\Storefront\GalleryController;
use App\Http\Controllers\Storefront\LoyaltyController;
use App\Http\Controllers\Storefront\ReviewController;
use App\Http\Controllers\Storefront\SurveyController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\StripeConnectController;
use App\Http\Middleware\EnsureStorefrontEnabled;
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
    Route::get('/manifest.json', [StorefrontController::class, 'manifest'])->name('manifest');
    // Service worker removed — was caching stale pages after deploys
    Route::get('/icons/icon-{size}.png', [StorefrontController::class, 'appIcon'])->name('app.icon');

    // Impersonation token consumer (from central admin)
    Route::get('/impersonate/{token}', [ImpersonateController::class, 'consume'])
        ->name('impersonate.consume');

    // Stripe Connect OAuth
    Route::get('/stripe/connect', [StripeConnectController::class, 'redirect'])
        ->middleware('auth')
        ->name('stripe.connect');

    // Driver view (no auth, shared via link)
    Route::prefix('driver')->name('driver.')->group(function () {
        Route::get('/', [DriverController::class, 'index'])->name('index');
        Route::post('/{order}/delivered', [DriverController::class, 'markDelivered'])->name('delivered')->middleware('auth');
    });

    // Staff invitation routes (outside auth & storefront middleware)
    Route::get('/invite/{token}', [InvitationController::class, 'show'])->name('invitation.show');
    Route::post('/invite/{token}', [InvitationController::class, 'accept'])->name('invitation.accept')->middleware('throttle:5,1');

    // Storefront routes — only accessible when storefront is enabled
    // When disabled, these redirect to the external website or show a minimal page
    Route::middleware([EnsureStorefrontEnabled::class, TrackPageView::class])->group(function () {
        Route::get('/menu', [StorefrontController::class, 'menu'])->name('storefront.menu');
        Route::get('/order', [OrderController::class, 'index'])->name('order.create');
        Route::post('/order', [OrderController::class, 'store'])->name('order.store')->middleware('throttle:10,1');
        Route::get('/order/confirmation/{order}', [OrderController::class, 'confirmation'])->name('order.confirmation');
        Route::get('/order/stripe/success/{order}', [OrderController::class, 'stripeSuccess'])->name('order.stripe.success');
        Route::get('/order/stripe/cancel/{order}', [OrderController::class, 'stripeCancel'])->name('order.stripe.cancel');
        Route::get('/about', [StorefrontController::class, 'about'])->name('storefront.about');
        Route::get('/reviews', [StorefrontController::class, 'reviews'])->name('storefront.reviews');
        Route::get('/gallery', [GalleryController::class, 'gallery'])->name('storefront.gallery');
        Route::post('/gallery', [GalleryController::class, 'submitPhoto'])->name('gallery.submit');
        Route::get('/track', [OrderController::class, 'track'])->name('order.track');
        Route::post('/track', [OrderController::class, 'trackLookup'])->name('order.track.lookup');

        // Order messages
        Route::get('/order/{order}/messages', [OrderController::class, 'messages'])->name('order.messages');
        Route::post('/order/{order}/messages', [OrderController::class, 'sendMessage'])->name('order.messages.send');
        Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
        Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

        // Reorder data (AJAX)
        Route::get('/order/reorder/{order}', [OrderController::class, 'reorderData'])->name('order.reorder');

        // Capacity check (AJAX)
        Route::get('/capacity/check/{date}', [OrderController::class, 'checkCapacity'])->name('capacity.check');

        // Availability for next 30 days (scheduling integration)
        Route::get('/availability', [OrderController::class, 'availability'])->name('order.availability');

        // Loyalty rewards
        Route::get('/rewards', [LoyaltyController::class, 'rewards'])->name('storefront.rewards');
        Route::post('/rewards/check', [LoyaltyController::class, 'checkRewards'])->name('rewards.check');

        // Gift Cards
        Route::get('/gift-cards', [StorefrontController::class, 'giftCards'])->name('storefront.gift-cards');
        Route::post('/gift-cards/purchase', [StorefrontController::class, 'purchaseGiftCard'])->name('gift-cards.purchase')->middleware('throttle:5,1');
        Route::post('/gift-cards/balance', [StorefrontController::class, 'checkGiftCardBalance'])->name('gift-cards.balance');

        // Coupon validation (AJAX)
        Route::post('/coupon/apply', [OrderController::class, 'applyCoupon'])->name('coupon.apply');

        // Gift card validation (AJAX)
        Route::post('/gift-card/apply', [OrderController::class, 'applyGiftCard'])->name('gift-card.apply');

        // Customer favorites (AJAX)
        Route::get('/favorites', [StorefrontController::class, 'getFavorites'])->name('favorites.get');
        Route::post('/favorites/toggle', [StorefrontController::class, 'toggleFavorite'])->name('favorites.toggle');

        // Catering
        Route::get('/catering', [CateringController::class, 'catering'])->name('storefront.catering');
        Route::post('/catering', [CateringController::class, 'submitCateringInquiry'])->name('catering.submit');

        // Blog
        Route::get('/blog', [StorefrontController::class, 'blog'])->name('storefront.blog');
        Route::get('/blog/feed.xml', [StorefrontController::class, 'blogFeed'])->name('storefront.blog.feed');
        Route::get('/blog/{slug}', [StorefrontController::class, 'blogPost'])->name('storefront.blog.show');

        // Review submission (from email link)
        Route::get('/review/{order}', [ReviewController::class, 'submitReview'])->name('storefront.submit-review');
        Route::post('/review/{order}', [ReviewController::class, 'storeReview'])->name('storefront.store-review');

        // Surveys
        Route::get('/survey/{survey}', [SurveyController::class, 'survey'])->name('storefront.survey');
        Route::post('/survey/{survey}', [SurveyController::class, 'submitSurvey'])->name('survey.submit');

        // Product waitlist
        Route::post('/waitlist/product', [StorefrontController::class, 'joinProductWaitlist'])->name('product-waitlist.join');
    });

    // Tenant Storefront API (JSON, no CSRF)
    Route::prefix('api')
        ->withoutMiddleware(VerifyCsrfToken::class)
        ->group(function () {
            // Read endpoints — generous limit
            Route::middleware('throttle:60,1')->group(function () {
                Route::get('/store', [StorefrontApiController::class, 'store']);
                Route::get('/categories', [StorefrontApiController::class, 'categories']);
                Route::get('/products', [StorefrontApiController::class, 'products']);
                Route::get('/menu', [StorefrontApiController::class, 'menu']);
                Route::get('/capacity/{date}', [StorefrontApiController::class, 'capacity']);
                Route::get('/reviews', [StorefrontApiController::class, 'reviews']);
                Route::get('/gallery', [StorefrontApiController::class, 'gallery']);
                Route::get('/favorites', [StorefrontApiController::class, 'favorites']);
            });

            // Write endpoints — tighter limit
            Route::middleware('throttle:10,1')->group(function () {
                Route::post('/orders', [StorefrontApiController::class, 'submitOrder']);
                Route::post('/coupon/validate', [StorefrontApiController::class, 'validateCoupon']);
                Route::post('/reviews', [StorefrontApiController::class, 'submitReview']);
                Route::post('/contact', [StorefrontApiController::class, 'submitContact']);
                Route::post('/favorites/toggle', [StorefrontApiController::class, 'toggleFavorite']);
                Route::post('/waitlist', [StorefrontApiController::class, 'waitlist']);
            });
        });
});
