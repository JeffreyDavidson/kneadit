<?php

$storefrontUrl = Illuminate\Support\Facades\Config::string('browser-testing.storefront_url');

test('contact page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/contact")
        ->assertVisible('[data-test="page-contact-show"]')
        ->assertNoJavaScriptErrors();
})->group('launch-smoke');
