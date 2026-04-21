<?php

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://sweet-surrender.kneadit.test');

test('gallery upload form blocks empty submit via HTML5 required', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/gallery")
        ->press('Submit Photo')
        ->assertPathIs('/gallery')
        ->assertVisible('[data-test="gallery-upload-form"]')
        ->assertNoJavaScriptErrors();
});

test('gallery upload form submits a photo and shows the success flash', function () use ($storefrontUrl) {
    // Provide a fixture image to attach. Browser tests run from project root so
    // the public/images logo doubles as a known-good 1x1+ PNG.
    $fixture = base_path('public/images/logo-icon.png');

    visit("{$storefrontUrl}/gallery")
        ->fill('[data-test="gallery-upload-form-customer-name"]', 'Test Photographer')
        ->fill('[data-test="gallery-upload-form-customer-email"]', 'photographer@example.com')
        ->attach('[data-test="gallery-upload-form-photo"]', $fixture)
        ->fill('[data-test="gallery-upload-form-caption"]', 'My birthday cake!')
        ->press('Submit Photo')
        ->assertSee('Thank you')
        ->assertNoJavaScriptErrors();
});
