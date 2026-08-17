<?php

use Illuminate\Support\Facades\Config;

$storefrontUrl = Config::string('browser-testing.storefront_url');

test('about page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/about")
        ->assertVisible('[data-test="page-about"]')
        ->assertNoJavaScriptErrors();
});
