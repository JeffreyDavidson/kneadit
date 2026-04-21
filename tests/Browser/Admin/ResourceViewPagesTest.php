<?php

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://sweet-surrender.kneadit.test');

// Smoke tests for admin resource view (detail) pages. Uses fixture IDs
// captured by prepare-admin-session.py — no DB access needed from the test.
//
// Only two Filament resources in this panel expose a view page:
//   - orders  (admin/orders/{id})
//   - surveys (admin/surveys/{id})
//
// Other resources (products, customers, etc.) don't have dedicated view
// pages — they use inline modal actions instead.
//
// Prerequisites:
//   1. BrowserTestFixtureSeeder has been run against the target tenant.
//   2. python3 tests/Browser/Helpers/prepare-admin-session.py

test('admin order view page renders cleanly', function () use ($storefrontUrl) {
    $id = fixtureId('review_order_id');

    authenticatedVisit("{$storefrontUrl}/admin/orders/{$id}")
        ->assertSee('BROWSER-TEST-REVIEW')
        ->assertNoJavaScriptErrors();
});

test('admin survey view page renders cleanly', function () use ($storefrontUrl) {
    $id = fixtureId('survey_id');

    authenticatedVisit("{$storefrontUrl}/admin/surveys/{$id}")
        ->assertSee('Browser Test Survey')
        ->assertNoJavaScriptErrors();
});
