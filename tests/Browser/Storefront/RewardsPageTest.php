<?php

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://sweet-surrender.kneadit.test');

test('rewards page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/rewards")
        ->assertVisible('[data-test="page-rewards"]')
        ->assertNoJavaScriptErrors();
});
