<?php

$storefrontUrl = Illuminate\Support\Facades\Config::string('browser-testing.storefront_url');

test('home page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/")
        ->assertVisible('[data-test="page-home"]')
        ->assertNoJavaScriptErrors();
})->group('launch-smoke');

test('home page renders cleanly on mobile', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/")
        ->on()->mobile()
        ->assertVisible('[data-test="page-home"]')
        ->assertNoJavaScriptErrors();
});
