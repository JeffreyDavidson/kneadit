<?php

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://browser-test.kneadit.test');

test('track page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/track")
        ->assertVisible('[data-test="page-order-track"]')
        ->assertNoJavaScriptErrors();
});
