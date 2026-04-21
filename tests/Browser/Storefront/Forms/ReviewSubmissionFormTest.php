<?php

use Database\Seeders\BrowserTestFixtureSeeder;

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://sweet-surrender.kneadit.test');
$orderNumber = BrowserTestFixtureSeeder::REVIEW_ORDER_NUMBER;

// Full happy-path review submission isn't tested here — each submission creates
// a real Review row in the tenant DB, which would accumulate over time. Covered:
// form presence + empty-rating server-side validation guard.
//
// Prerequisite: BrowserTestFixtureSeeder has been run against the target tenant
// (see database/seeders/BrowserTestFixtureSeeder.php).

test('review submission form is visible on the page', function () use ($storefrontUrl, $orderNumber) {
    visit("{$storefrontUrl}/review/{$orderNumber}")
        ->assertVisible('[data-test="review-submission-form"]')
        ->assertVisible('[data-test="review-submission-form-rating-1"]')
        ->assertVisible('[data-test="review-submission-form-rating-5"]')
        ->assertVisible('[data-test="review-submission-form-comment"]')
        ->assertVisible('[data-test="review-submission-form-submit"]')
        ->assertNoJavaScriptErrors();
});

test('review submission form rejects submit without a rating', function () use ($storefrontUrl, $orderNumber) {
    visit("{$storefrontUrl}/review/{$orderNumber}")
        ->press('Submit Review')
        ->assertPathIs("/review/{$orderNumber}")
        ->assertVisible('[data-test="review-submission-form"]')
        ->assertNoJavaScriptErrors();
});
