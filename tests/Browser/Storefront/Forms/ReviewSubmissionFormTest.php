<?php

use Database\Seeders\BrowserTestFixtureSeeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

$storefrontUrl = Config::string('browser-testing.storefront_url');
$orderNumber = BrowserTestFixtureSeeder::REVIEW_ORDER_NUMBER;

// Full happy-path review submission isn't tested here — each submission creates
// a real Review row in the tenant DB, which would accumulate over time. Covered:
// form presence + empty-rating server-side validation guard.
//
// Prerequisite: BrowserTestFixtureSeeder has been run against the target tenant
// (see database/seeders/BrowserTestFixtureSeeder.php).

// The review GET route is signed-URL-gated; the email link is the proof of
// ownership. Tests build a fresh signed URL for each visit, against the
// browser-test tenant domain over https — Herd 301-redirects http to https,
// which would invalidate the signature, so URL generation must match the
// scheme the request will actually arrive on.
$signedReviewUrl = function () use ($storefrontUrl, $orderNumber): string {
    URL::forceRootUrl(str_replace('http://', 'https://', $storefrontUrl));
    URL::forceScheme('https');

    try {
        return URL::temporarySignedRoute(
            'storefront.submitReview',
            now()->addHour(),
            ['order' => $orderNumber],
        );
    } finally {
        URL::forceRootUrl(null);
    }
};

test('review submission form is visible on the page', function () use ($signedReviewUrl) {
    visit($signedReviewUrl())
        ->assertVisible('[data-test="review-submission-form"]')
        ->assertVisible('[data-test="review-submission-form-rating-1"]')
        ->assertVisible('[data-test="review-submission-form-rating-5"]')
        ->assertVisible('[data-test="review-submission-form-comment"]')
        ->assertVisible('[data-test="review-submission-form-submit"]')
        ->assertNoJavaScriptErrors();
});

test('review submission form rejects submit without a rating', function () use ($signedReviewUrl, $orderNumber) {
    visit($signedReviewUrl())
        ->press('Submit Review')
        ->assertPathIs("/review/{$orderNumber}")
        ->assertVisible('[data-test="review-submission-form"]')
        ->assertNoJavaScriptErrors();
});
