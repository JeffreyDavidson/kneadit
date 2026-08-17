<?php

use Illuminate\Support\Facades\Config;

$storefrontUrl = Config::string('browser-testing.storefront_url');

test('menu page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/menu")
        ->assertVisible('[data-test="page-menu"]')
        ->assertNoJavaScriptErrors();
})->group('launch-smoke');
