<?php

use Database\Seeders\BrowserTestFixtureSeeder;

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://sweet-surrender.kneadit.test');

// End-to-end login flow using the seeded admin user. Confirms the credentials
// are valid, the session cookie is set, and Filament's panel middleware accepts
// the Owner role. The seeded fixture admin has not completed onboarding, so the
// post-login destination is /admin/onboarding — that's the authenticated landmark
// we assert here.
//
// Prerequisite: BrowserTestFixtureSeeder has been run against the target tenant.

test('tenant admin login with seeded credentials lands on onboarding', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/admin/login")
        ->fill('input[type="email"]', BrowserTestFixtureSeeder::ADMIN_EMAIL)
        ->fill('input[type="password"]', BrowserTestFixtureSeeder::ADMIN_PASSWORD)
        ->click('button[type="submit"]')
        ->wait(3)
        ->assertSee('Welcome to KneadIt')
        ->assertNoJavaScriptErrors();
});
