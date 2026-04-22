<?php

use Database\Seeders\BrowserTestFixtureSeeder;

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://browser-test.kneadit.test');

// End-to-end login flow using the seeded admin user. Confirms the credentials
// are valid, the session cookie is set, Filament's panel middleware accepts
// the Owner role, and the seeder's onboarding_completed_at skip works —
// the admin lands on the bakery dashboard, not /admin/onboarding.
//
// Prerequisite: BrowserTestFixtureSeeder has been run against the target tenant.

test('tenant admin login with seeded credentials lands on the bakery dashboard', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/admin/login")
        ->fill('input[type="email"]', BrowserTestFixtureSeeder::ADMIN_EMAIL)
        ->fill('input[type="password"]', BrowserTestFixtureSeeder::ADMIN_PASSWORD)
        ->click('button[type="submit"]')
        ->wait(3)
        ->assertSee('Bakery Dashboard')
        ->assertNoJavaScriptErrors();
});
