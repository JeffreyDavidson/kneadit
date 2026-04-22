<?php

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://browser-test.kneadit.test');

test('blog index page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/blog")
        ->assertVisible('[data-test="page-blog"]')
        ->assertNoJavaScriptErrors();
});
