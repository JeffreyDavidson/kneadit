<?php

$storefrontUrl = Illuminate\Support\Facades\Config::string('browser-testing.storefront_url');

test('reviews page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/reviews")
        ->assertVisible('[data-test="page-reviews"]')
        ->assertNoJavaScriptErrors();
});
