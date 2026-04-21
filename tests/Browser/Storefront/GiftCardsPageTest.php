<?php

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://sweet-surrender.kneadit.test');

test('gift cards page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/gift-cards")
        ->assertVisible('[data-test="page-giftCards"]')
        ->assertNoJavaScriptErrors();
});
