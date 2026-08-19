<?php

$storefrontUrl = Illuminate\Support\Facades\Config::string('browser-testing.storefront_url');

test('blog index page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/blog")
        ->assertVisible('[data-test="page-blog"]')
        ->assertNoJavaScriptErrors();
});
