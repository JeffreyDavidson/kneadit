<?php

$centralUrl = env('BROWSER_TEST_CENTRAL_URL', 'http://kneadit.test');

// Smoke test for the landlord (central) admin dashboard. Uses the central
// admin storage state captured by prepare-admin-session.py alongside the
// tenant admin state.
//
// Prerequisites:
//   1. php artisan db:seed --class="Database\\Seeders\\BrowserTestCentralFixtureSeeder"
//   2. python3 tests/Browser/Helpers/prepare-admin-session.py

test('central admin dashboard renders without JS errors', function () use ($centralUrl) {
    authenticatedCentralVisit("{$centralUrl}/admin")
        ->assertSee('KneadIt Platform')
        ->assertNoJavaScriptErrors();
});
