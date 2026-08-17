<?php

use Illuminate\Support\Facades\Config;

$storefrontUrl = Config::string('browser-testing.storefront_url');

// Filament's login page is rendered by the framework, so we don't own the markup
// and can't add data-test attributes. Selectors here key off input type and
// button text — the canonical Filament login shape.

test('tenant admin login page renders without JS errors', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/admin/login")
        ->assertVisible('input[type="email"]')
        ->assertVisible('input[type="password"]')
        ->assertSee('Sign in')
        ->assertNoJavaScriptErrors();
});

test('tenant admin login with empty fields stays on login page', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/admin/login")
        ->press('Sign in')
        ->assertPathIs('/admin/login')
        ->assertNoJavaScriptErrors();
});
