<?php

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://sweet-surrender.kneadit.test');

test('about page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/about")
        ->assertVisible('[data-test="page-about"]')
        ->assertNoJavaScriptErrors();
});
