<?php

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://sweet-surrender.kneadit.test');

test('order page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/order")
        ->assertVisible('[data-test="page-order-create"]')
        ->assertNoJavaScriptErrors();
});
