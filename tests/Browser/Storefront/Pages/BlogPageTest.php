<?php

use Illuminate\Support\Facades\Config;

$storefrontUrl = Config::string('browser-testing.storefront_url');

test('blog index page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/blog")
        ->assertVisible('[data-test="page-blog"]')
        ->assertNoJavaScriptErrors();
});
