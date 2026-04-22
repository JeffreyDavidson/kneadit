<?php

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://browser-test.kneadit.test');

test('register page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/account/register")
        ->assertVisible('[data-test="page-account-register-show"]')
        ->assertNoJavaScriptErrors();
});
