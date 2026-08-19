<?php

$storefrontUrl = Illuminate\Support\Facades\Config::string('browser-testing.storefront_url');

test('order page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/order")
        ->assertVisible('[data-test="page-order-create"]')
        ->assertNoJavaScriptErrors();
})->group('launch-smoke');
