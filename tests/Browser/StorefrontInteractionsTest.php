<?php

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://sweet-surrender.kneadit.test');
$centralUrl = env('BROWSER_TEST_CENTRAL_URL', 'http://kneadit.test');

test('smoke test all storefront pages', function () use ($storefrontUrl) {
    $pages = visit([
        "{$storefrontUrl}/",
        "{$storefrontUrl}/menu",
        "{$storefrontUrl}/about",
        "{$storefrontUrl}/contact",
        "{$storefrontUrl}/reviews",
        "{$storefrontUrl}/gallery",
        "{$storefrontUrl}/order",
        "{$storefrontUrl}/catering",
        "{$storefrontUrl}/rewards",
        "{$storefrontUrl}/gift-cards",
        "{$storefrontUrl}/track",
    ]);

    $pages->assertNoJavaScriptErrors();
});

test('smoke test central pages', function () use ($centralUrl) {
    $pages = visit([
        "{$centralUrl}",
        "{$centralUrl}/directory",
        "{$centralUrl}/changelog",
        "{$centralUrl}/resources",
    ]);

    $pages->assertNoJavaScriptErrors();
});

test('order page loads without errors', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/order")
        ->assertNoJavaScriptErrors();
});

test('storefront renders on mobile', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/")
        ->on()->mobile()
        ->assertNoJavaScriptErrors();
});
