<?php

use Illuminate\Support\Facades\Config;

$storefrontUrl = Config::string('browser-testing.storefront_url');

test('login page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/account/login")
        ->assertVisible('[data-test="page-account-login-show"]')
        ->assertNoJavaScriptErrors();
});
