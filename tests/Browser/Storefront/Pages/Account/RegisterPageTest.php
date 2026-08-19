<?php

$storefrontUrl = Illuminate\Support\Facades\Config::string('browser-testing.storefront_url');

test('register page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/account/register")
        ->assertVisible('[data-test="page-account-register-show"]')
        ->assertNoJavaScriptErrors();
});
