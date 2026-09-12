<?php

declare(strict_types=1);

use App\Http\Controllers\Catering\CateringStripeCancelController;
use App\Http\Controllers\Catering\CateringStripeSuccessController;
use App\Http\Controllers\Catering\PayCateringDepositController;
use App\Http\Controllers\Storefront\AboutController;
use App\Http\Controllers\Storefront\BlogController as StorefrontBlogController;
use App\Http\Controllers\Storefront\BlogFeedController as StorefrontBlogFeedController;
use App\Http\Controllers\Storefront\CheckGiftCardBalanceController;
use App\Http\Controllers\Storefront\ContactController;
use App\Http\Controllers\Storefront\GalleryController;
use App\Http\Controllers\Storefront\LoyaltyController;
use App\Http\Controllers\Storefront\MenuController;
use App\Http\Controllers\Storefront\ProductWaitlistController;
use App\Http\Controllers\Storefront\PurchaseGiftCardController;
use App\Http\Controllers\Storefront\ReviewsIndexController;
use App\Http\Controllers\Storefront\ShowCateringController;
use App\Http\Controllers\Storefront\ShowGiftCardsController;
use App\Http\Controllers\Storefront\ShowReviewFormController;
use App\Http\Controllers\Storefront\StoreReviewController;
use App\Http\Controllers\Storefront\SubmitCateringInquiryController;
use App\Http\Controllers\Storefront\SurveyController;
use App\Routing\Resolvers\ActiveSurveyResolver;
use App\Routing\Resolvers\PublishedTenantBlogPostResolver;
use Illuminate\Support\Facades\Route;

Route::bind('post', resolve(PublishedTenantBlogPostResolver::class));
Route::bind('survey', resolve(ActiveSurveyResolver::class));

Route::get('menu', MenuController::class)->name('storefront.menu');
Route::get('about', AboutController::class)->name('storefront.about');
Route::get('reviews', ReviewsIndexController::class)->name('storefront.reviews');
Route::get('gallery', [GalleryController::class, 'show'])->name('storefront.gallery');
Route::post('gallery', [GalleryController::class, 'store'])->name('gallery.submit')->middleware('throttle:form-write');

Route::get('contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('contact', [ContactController::class, 'store'])->name('contact.store')->middleware('throttle:form-write');

// Loyalty rewards
Route::get('rewards', [LoyaltyController::class, 'show'])->name('storefront.rewards');
Route::post('rewards/check', [LoyaltyController::class, 'store'])->name('rewards.check')->middleware('throttle:form-write');

// Gift Cards
Route::get('gift-cards', ShowGiftCardsController::class)->name('storefront.giftCards');
Route::post('gift-cards/purchase', PurchaseGiftCardController::class)->name('giftCards.purchase')->middleware('throttle:sensitive-write');
Route::post('gift-cards/balance', CheckGiftCardBalanceController::class)->name('giftCards.balance')->middleware('throttle:form-write');

// Catering
Route::get('catering', ShowCateringController::class)->name('storefront.catering');
Route::post('catering', SubmitCateringInquiryController::class)->name('catering.submit')->middleware('throttle:form-write');

// Catering deposit via Stripe Checkout (signed link from quote email).
Route::get('catering/{inquiry}/pay-deposit', PayCateringDepositController::class)
    ->name('catering.payDeposit')
    ->middleware('signed');

// Stripe redirect callbacks.
Route::get('catering/stripe/success/{inquiry}', CateringStripeSuccessController::class)
    ->name('catering.stripe.success');
Route::get('catering/stripe/cancel/{inquiry}', CateringStripeCancelController::class)
    ->name('catering.stripe.cancel');

// Blog
Route::get('blog', [StorefrontBlogController::class, 'index'])->name('storefront.blog');
Route::get('blog/feed.xml', StorefrontBlogFeedController::class)->name('storefront.blog.feed');
Route::get('blog/{post:slug}', [StorefrontBlogController::class, 'show'])->name('storefront.blog.show');

// Review submission (from email link). Email link is a signed URL — that's the
// proof of order ownership for the GET. The GET handler grants session access
// so the POST passes through the order.access gate without re-signing.
Route::get('review/{order:order_number}', ShowReviewFormController::class)
    ->middleware('signed')
    ->name('storefront.submitReview');
Route::post('review/{order:order_number}', StoreReviewController::class)
    ->middleware(['order.access', 'throttle:form-write'])
    ->name('storefront.storeReview');

// Surveys
Route::get('survey/{survey}', [SurveyController::class, 'show'])->name('storefront.survey');
Route::post('survey/{survey}', [SurveyController::class, 'store'])->name('survey.submit')->middleware('throttle:form-write');

// Product waitlist
Route::post('waitlist/product', ProductWaitlistController::class)->name('productWaitlist.join')->middleware('throttle:form-write');

// Campaign open tracking pixel — public, returns a 1x1 GIF.
Route::get('track/email-open/{token}.gif', App\Http\Controllers\Marketing\TrackCampaignOpenController::class)
    ->name('campaign.track.open')
    ->where('token', '[A-Z0-9]{26}');
