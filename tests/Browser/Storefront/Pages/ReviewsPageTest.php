<?php

use Illuminate\Support\Facades\Config;

$storefrontUrl = Config::string('browser-testing.storefront_url');

test('reviews page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/reviews")
        ->assertVisible('[data-test="page-reviews"]')
        ->assertNoJavaScriptErrors();
});
