<?php

$storefrontUrl = Illuminate\Support\Facades\Config::string('browser-testing.storefront_url');

test('forgot-password page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/account/forgot-password")
        ->assertVisible('[data-test="page-account-password-request"]')
        ->assertNoJavaScriptErrors();
});
