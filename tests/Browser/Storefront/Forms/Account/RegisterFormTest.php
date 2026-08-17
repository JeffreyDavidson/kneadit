<?php

use Illuminate\Support\Facades\Config;

$storefrontUrl = Config::string('browser-testing.storefront_url');

test('register form blocks empty submit via HTML5 required', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/account/register")
        ->click('[data-test="register-form-submit"]')
        ->assertPathIs('/account/register')
        ->assertVisible('[data-test="register-form"]')
        ->assertNoJavaScriptErrors();
});

test('register form server-side validation rejects mismatched password confirmation', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/account/register")
        ->fill('[data-test="register-form-name"]', 'Test User')
        ->fill('[data-test="register-form-email"]', 'mismatch-' . uniqid() . '@example.com')
        ->fill('[data-test="register-form-password"]', 'password1234')
        ->fill('[data-test="register-form-password-confirmation"]', 'different-password')
        ->click('[data-test="register-form-submit"]')
        // Server should bounce back to /account/register with validation errors
        ->assertPathIs('/account/register')
        ->assertVisible('[data-test="register-form"]')
        ->assertNoJavaScriptErrors();
});

// Note: a successful registration test (which creates a real customer) belongs in
// a future Flows/ tier alongside email verify + login, with proper test-data cleanup.
// This file covers form-level validation only.
