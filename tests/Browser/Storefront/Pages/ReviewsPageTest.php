<?php

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://sweet-surrender.kneadit.test');

test('reviews page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/reviews")
        ->assertVisible('[data-test="page-reviews"]')
        ->assertNoJavaScriptErrors();
});
