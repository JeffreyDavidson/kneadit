<?php

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://sweet-surrender.kneadit.test');

test('gallery page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/gallery")
        ->assertVisible('[data-test="page-gallery"]')
        ->assertNoJavaScriptErrors();
});
