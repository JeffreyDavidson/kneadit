<?php

use Illuminate\Support\Facades\Config;

$storefrontUrl = Config::string('browser-testing.storefront_url');

test('gallery page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/gallery")
        ->assertVisible('[data-test="page-gallery"]')
        ->assertNoJavaScriptErrors();
});
