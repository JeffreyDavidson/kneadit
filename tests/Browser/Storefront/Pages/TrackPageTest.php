<?php

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://browser-test.kneadit.test');

test('track page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/track")
        ->assertVisible('[data-test="page-order-track"]')
        ->assertNoJavaScriptErrors();
});

// Uses an address with no orders, so no email is queued.
test('track form asks the customer to check their email', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/track")
        ->fill('#email', 'browser-test-no-orders@kneadit.test')
        ->click('button[type="submit"]')
        ->assertSee("we've sent you a link to view them")
        ->assertDontSee('No orders found')
        ->assertNoJavaScriptErrors();
})->group('launch-smoke');
