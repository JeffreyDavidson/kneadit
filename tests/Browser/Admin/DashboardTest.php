<?php

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://sweet-surrender.kneadit.test');

// Dashboard smoke test. The login flow test only checks the heading is visible
// immediately after redirect — it doesn't wait for widgets to poll and render,
// so widget-level errors (500s on Livewire update, cache-related crashes, etc.)
// can slip through. This test explicitly visits /admin post-auth and asserts
// zero JS errors after full page settlement.
//
// Prerequisites (same as other authenticated admin tests):
//   1. BrowserTestFixtureSeeder has been run.
//   2. python3 tests/Browser/Helpers/prepare-admin-session.py

test('admin dashboard renders all widgets cleanly', function () use ($storefrontUrl) {
    authenticatedVisit("{$storefrontUrl}/admin")
        ->assertSee('Bakery Dashboard')
        ->assertNoJavaScriptErrors();
});
