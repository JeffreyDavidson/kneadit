<?php

$storefrontUrl = 'http://sweet-surrender.kneadit.test';

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

test('smoke test central pages', function () {
    $pages = visit([
        'http://kneadit.test',
        'http://kneadit.test/directory',
        'http://kneadit.test/changelog',
        'http://kneadit.test/resources',
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
