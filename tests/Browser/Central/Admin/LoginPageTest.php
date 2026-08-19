<?php

$centralUrl = Illuminate\Support\Facades\Config::string('browser-testing.central_url');

// Filament's login page is rendered by the framework, so we don't own the markup
// and can't add data-test attributes. Selectors here key off input type and
// button text — the canonical Filament login shape.

test('central admin login page renders without JS errors', function () use ($centralUrl) {
    visit("{$centralUrl}/admin/login")
        ->assertVisible('input[type="email"]')
        ->assertVisible('input[type="password"]')
        ->assertNoJavaScriptErrors();
});

test('central admin login with empty fields stays on login page', function () use ($centralUrl) {
    visit("{$centralUrl}/admin/login")
        ->press('Sign in')
        ->assertPathIs('/admin/login')
        ->assertNoJavaScriptErrors();
});
