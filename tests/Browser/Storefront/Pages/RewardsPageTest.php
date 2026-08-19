<?php

$storefrontUrl = Illuminate\Support\Facades\Config::string('browser-testing.storefront_url');

test('rewards page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/rewards")
        ->assertVisible('[data-test="page-rewards"]')
        ->assertNoJavaScriptErrors();
});
