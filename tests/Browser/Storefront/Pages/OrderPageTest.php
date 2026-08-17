<?php

use Illuminate\Support\Facades\Config;

$storefrontUrl = Config::string('browser-testing.storefront_url');

test('order page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/order")
        ->assertVisible('[data-test="page-order-create"]')
        ->assertNoJavaScriptErrors();
})->group('launch-smoke');
