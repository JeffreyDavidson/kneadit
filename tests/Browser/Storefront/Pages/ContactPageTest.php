<?php

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://browser-test.kneadit.test');

test('contact page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/contact")
        ->assertVisible('[data-test="page-contact-show"]')
        ->assertNoJavaScriptErrors();
})->group('launch-smoke');
