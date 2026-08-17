<?php

use Illuminate\Support\Facades\Config;

$storefrontUrl = Config::string('browser-testing.storefront_url');

test('track page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/track")
        ->assertVisible('[data-test="page-order-track"]')
        ->assertNoJavaScriptErrors();
});
