<?php

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://sweet-surrender.kneadit.test');

test('login form blocks empty submit via HTML5 required', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/account/login")
        ->click('[data-test="login-form-submit"]')
        ->assertPathIs('/account/login')
        ->assertVisible('[data-test="login-form"]')
        ->assertNoJavaScriptErrors();
});

test('login form rejects invalid credentials and stays on the login page', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/account/login")
        ->fill('[data-test="login-form-email"]', 'nobody@example.com')
        ->fill('[data-test="login-form-password"]', 'wrong-password-1234')
        ->click('[data-test="login-form-submit"]')
        ->assertPathIs('/account/login')
        ->assertVisible('[data-test="login-form"]')
        ->assertNoJavaScriptErrors();
});
