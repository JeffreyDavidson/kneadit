<?php

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://browser-test.kneadit.test');

test('catering page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/catering")
        ->assertVisible('[data-test="page-catering"]')
        ->assertNoJavaScriptErrors();
});
