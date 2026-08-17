<?php

use Illuminate\Support\Facades\Config;

$storefrontUrl = Config::string('browser-testing.storefront_url');

// Filament's password-reset-request page is rendered by the framework, so we
// don't own the markup and can't add data-test attributes. Selectors here key
// off input type and button text.
//
// The /admin/password-reset/reset endpoint is not covered here — it requires a
// valid signed token and 404s without one.

test('tenant admin password reset request page renders without JS errors', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/admin/password-reset/request")
        ->assertVisible('input[type="email"]')
        ->assertSee('Send email')
        ->assertNoJavaScriptErrors();
});

test('tenant admin password reset request with empty email stays on page', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/admin/password-reset/request")
        ->press('Send email')
        ->assertPathIs('/admin/password-reset/request')
        ->assertNoJavaScriptErrors();
});
