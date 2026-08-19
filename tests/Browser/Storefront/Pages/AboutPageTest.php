<?php

$storefrontUrl = Illuminate\Support\Facades\Config::string('browser-testing.storefront_url');

test('about page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/about")
        ->assertVisible('[data-test="page-about"]')
        ->assertNoJavaScriptErrors();
});
