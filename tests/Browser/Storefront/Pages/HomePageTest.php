<?php

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://browser-test.kneadit.test');

test('home page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/")
        ->assertVisible('[data-test="page-home"]')
        ->assertNoJavaScriptErrors();
});

test('home page renders cleanly on mobile', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/")
        ->on()->mobile()
        ->assertVisible('[data-test="page-home"]')
        ->assertNoJavaScriptErrors();
});
