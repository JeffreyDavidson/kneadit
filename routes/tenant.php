<?php

declare(strict_types=1);

use App\Http\Controllers\Api\CapacityController as ApiCapacityController;
use App\Http\Controllers\Api\CategoryController as ApiCategoryController;
use App\Http\Controllers\Api\ContactController as ApiContactController;
use App\Http\Controllers\Api\CouponValidationController;
use App\Http\Controllers\Api\FavoriteController as ApiFavoriteController;
use App\Http\Controllers\Api\GalleryController as ApiGalleryController;
use App\Http\Controllers\Api\MenuController as ApiMenuController;
use App\Http\Controllers\Api\OrderController as ApiOrderController;
use App\Http\Controllers\Api\ProductController as ApiProductController;
use App\Http\Controllers\Api\ReviewController as ApiReviewController;
use App\Http\Controllers\Api\StoreInfoController;
use App\Http\Controllers\Api\WaitlistController as ApiWaitlistController;
use App\Http\Controllers\ConsumeImpersonationController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\Order\ApplyCouponController;
use App\Http\Controllers\Order\ApplyGiftCardController;
use App\Http\Controllers\Order\AvailabilityController;
use App\Http\Controllers\Order\CapacityController;
use App\Http\Controllers\Order\MessageController;
use App\Http\Controllers\Order\ReorderController;
use App\Http\Controllers\Order\StripeCancelController;
use App\Http\Controllers\Order\StripeSuccessController;
use App\Http\Controllers\Order\TrackingController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Storefront\AboutController;
use App\Http\Controllers\Storefront\AppIconController;
use App\Http\Controllers\Storefront\BlogController as StorefrontBlogController;
use App\Http\Controllers\Storefront\BlogFeedController as StorefrontBlogFeedController;
use App\Http\Controllers\Storefront\CateringController;
use App\Http\Controllers\Storefront\CheckGiftCardBalanceController;
use App\Http\Controllers\Storefront\FavoriteController;
use App\Http\Controllers\Storefront\GalleryController;
use App\Http\Controllers\Storefront\LoyaltyController;
use App\Http\Controllers\Storefront\ManifestController;
use App\Http\Controllers\Storefront\MenuController;
use App\Http\Controllers\Storefront\ProductWaitlistController;
use App\Http\Controllers\Storefront\PurchaseGiftCardController;
use App\Http\Controllers\Storefront\ReviewController;
use App\Http\Controllers\Storefront\ReviewsController;
use App\Http\Controllers\Storefront\ShowGiftCardsController;
use App\Http\Controllers\Storefront\SurveyController;
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
    Route::get('/manifest.json', ManifestController::class)->name('manifest');
    // Service worker removed — was caching stale pages after deploys
    Route::get('/icons/icon-{size}.png', AppIconController::class)->name('app.icon');

    // Impersonation token consumer (from central admin)
    Route::get('/impersonate/{token}', ConsumeImpersonationController::class)
        ->name('impersonate.consume');

    // Stripe Connect OAuth
    Route::get('/stripe/connect', StripeConnectController::class)
        ->middleware('auth')
        ->name('stripe.connect');

    // Driver view (no auth, shared via link)
    Route::prefix('driver')->name('driver.')->group(function () {
        Route::get('/', [DriverController::class, 'index'])->name('index');
        Route::post('/{order}/delivered', [DriverController::class, 'update'])->name('delivered')->middleware('auth');
    });

    // Staff invitation routes (outside auth & storefront middleware)
    Route::get('/invite/{token}', [InvitationController::class, 'show'])->name('invitation.show');
    Route::post('/invite/{token}', [InvitationController::class, 'store'])->name('invitation.accept')->middleware('throttle:5,1');

    // Storefront routes — only accessible when storefront is enabled
    // When disabled, these redirect to the external website or show a minimal page
    Route::middleware([EnsureStorefrontEnabled::class, TrackPageView::class])->group(function () {
        Route::get('/menu', MenuController::class)->name('storefront.menu');
        Route::get('/order', [OrderController::class, 'index'])->name('order.create');
        Route::post('/order', [OrderController::class, 'store'])->name('order.store')->middleware('throttle:10,1');
        Route::get('/order/confirmation/{order}', [OrderController::class, 'show'])->name('order.confirmation');
        Route::get('/order/stripe/success/{order}', StripeSuccessController::class)->name('order.stripe.success');
        Route::get('/order/stripe/cancel/{order}', StripeCancelController::class)->name('order.stripe.cancel');
        Route::get('/about', AboutController::class)->name('storefront.about');
        Route::get('/reviews', ReviewsController::class)->name('storefront.reviews');
        Route::get('/gallery', [GalleryController::class, 'show'])->name('storefront.gallery');
        Route::post('/gallery', [GalleryController::class, 'store'])->name('gallery.submit')->middleware('throttle:10,1');
        Route::get('/track', [TrackingController::class, 'show'])->name('order.track');
        Route::post('/track', [TrackingController::class, 'store'])->name('order.track.lookup')->middleware('throttle:10,1');

        // Order messages
        Route::get('/order/{order}/messages', [MessageController::class, 'show'])->name('order.messages');
        Route::post('/order/{order}/messages', [MessageController::class, 'store'])->name('order.messages.send')->middleware('throttle:10,1');
        Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
        Route::post('/contact', [ContactController::class, 'store'])->name('contact.store')->middleware('throttle:10,1');

        // Reorder data (AJAX)
        Route::get('/order/reorder/{order}', ReorderController::class)->name('order.reorder');

        // Capacity check (AJAX)
        Route::get('/capacity/check/{date}', CapacityController::class)->name('capacity.check');

        // Availability for next 30 days (scheduling integration)
        Route::get('/availability', AvailabilityController::class)->name('order.availability');

        // Loyalty rewards
        Route::get('/rewards', [LoyaltyController::class, 'show'])->name('storefront.rewards');
        Route::post('/rewards/check', [LoyaltyController::class, 'store'])->name('rewards.check')->middleware('throttle:10,1');

        // Gift Cards
        Route::get('/gift-cards', ShowGiftCardsController::class)->name('storefront.gift-cards');
        Route::post('/gift-cards/purchase', PurchaseGiftCardController::class)->name('gift-cards.purchase')->middleware('throttle:5,1');
        Route::post('/gift-cards/balance', CheckGiftCardBalanceController::class)->name('gift-cards.balance')->middleware('throttle:10,1');

        // Coupon validation (AJAX)
        Route::post('/coupon/apply', ApplyCouponController::class)->name('coupon.apply')->middleware('throttle:10,1');

        // Gift card validation (AJAX)
        Route::post('/gift-card/apply', ApplyGiftCardController::class)->name('gift-card.apply')->middleware('throttle:10,1');

        // Customer favorites (AJAX)
        Route::get('/favorites', [FavoriteController::class, 'show'])->name('favorites.get');
        Route::post('/favorites/toggle', [FavoriteController::class, 'store'])->name('favorites.toggle')->middleware('throttle:10,1');

        // Catering
        Route::get('/catering', [CateringController::class, 'show'])->name('storefront.catering');
        Route::post('/catering', [CateringController::class, 'store'])->name('catering.submit')->middleware('throttle:10,1');

        // Blog
        Route::get('/blog', [StorefrontBlogController::class, 'index'])->name('storefront.blog');
        Route::get('/blog/feed.xml', StorefrontBlogFeedController::class)->name('storefront.blog.feed');
        Route::get('/blog/{post}', [StorefrontBlogController::class, 'show'])->name('storefront.blog.show');

        // Review submission (from email link)
        Route::get('/review/{order}', [ReviewController::class, 'show'])->name('storefront.submit-review');
        Route::post('/review/{order}', [ReviewController::class, 'store'])->name('storefront.store-review')->middleware('throttle:10,1');

        // Surveys
        Route::get('/survey/{survey}', [SurveyController::class, 'show'])->name('storefront.survey');
        Route::post('/survey/{survey}', [SurveyController::class, 'store'])->name('survey.submit')->middleware('throttle:10,1');

        // Product waitlist
        Route::post('/waitlist/product', ProductWaitlistController::class)->name('product-waitlist.join')->middleware('throttle:10,1');
    });

    // Tenant Storefront API (JSON, no CSRF)
    Route::prefix('api')
        ->withoutMiddleware(VerifyCsrfToken::class)
        ->group(function () {
            // Read endpoints — generous limit
            Route::middleware('throttle:60,1')->group(function () {
                Route::get('/store', StoreInfoController::class);
                Route::get('/categories', ApiCategoryController::class);
                Route::get('/products', ApiProductController::class);
                Route::get('/menu', ApiMenuController::class);
                Route::get('/capacity/{date}', ApiCapacityController::class);
                Route::get('/reviews', [ApiReviewController::class, 'index']);
                Route::get('/gallery', ApiGalleryController::class);
                Route::get('/favorites', [ApiFavoriteController::class, 'index']);
            });

            // Write endpoints — tighter limit
            Route::middleware('throttle:10,1')->group(function () {
                Route::post('/orders', ApiOrderController::class);
                Route::post('/coupon/validate', CouponValidationController::class);
                Route::post('/reviews', [ApiReviewController::class, 'store']);
                Route::post('/contact', ApiContactController::class);
                Route::post('/favorites/toggle', [ApiFavoriteController::class, 'store']);
                Route::post('/waitlist', ApiWaitlistController::class);
            });
        });
});
