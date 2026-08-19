<?php

$storefrontUrl = Illuminate\Support\Facades\Config::string('browser-testing.storefront_url');

test('catering page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/catering")
        ->assertVisible('[data-test="page-catering"]')
        ->assertNoJavaScriptErrors();
});
