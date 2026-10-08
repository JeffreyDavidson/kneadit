<?php

use Database\Seeders\BrowserTestFixtureSeeder;

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://browser-test.kneadit.test');

// Proves bakery settings are really stored: change the Store Phone, then see it
// again after a reload and after signing out and back in. The original value is
// put back at the end so reruns start clean.
//
// Prerequisite: BrowserTestFixtureSeeder has been run against the target tenant.
//
// Every navigate() gets its own timeout. Pest retries each chained call with a
// 1 second Playwright timeout, so a settings page that takes longer than that
// to load makes goto() time out and start again. The browser plugin can then
// read the timed-out goto's late reply as the answer to a later call, which
// shifts every reply after it by one: assertNoJavaScriptErrors() received the
// Store Phone string instead of the error list. A timeout long enough for the
// page to finish loading keeps each reply with the call that asked for it.

test('a saved Store Phone survives a reload and signing out and back in', function () use ($storefrontUrl) {
    // Typed and shown in the US national format; stored in E.164.
    $newPhone = '(913) 555-01'.random_int(10, 99);
    $settingsUrl = "{$storefrontUrl}/admin/manage-settings";
    $navigation = ['timeout' => 15_000];

    $page = visit("{$storefrontUrl}/admin/login")
        ->fill('input[type="email"]', BrowserTestFixtureSeeder::ADMIN_EMAIL)
        ->fill('input[type="password"]', BrowserTestFixtureSeeder::ADMIN_PASSWORD)
        ->click('button[type="submit"]')
        ->waitForEvent('networkidle')
        ->assertSee('Dashboard')
        ->navigate($settingsUrl, $navigation)
        ->assertSee('Store Information');

    $originalPhone = $page->value('input[id="content.store_phone"]');

    try {
        $page
            ->fill('input[id="content.store_phone"]', $newPhone)
            ->press('Save Settings')
            ->assertSee('Settings saved successfully!')
            ->navigate($settingsUrl, $navigation)
            ->assertValue('input[id="content.store_phone"]', $newPhone)
            ->click('button[aria-label="User menu"]')
            ->press('Sign out')
            ->waitForEvent('networkidle')
            ->assertPathIs('/admin/login')
            ->fill('input[type="email"]', BrowserTestFixtureSeeder::ADMIN_EMAIL)
            ->fill('input[type="password"]', BrowserTestFixtureSeeder::ADMIN_PASSWORD)
            ->click('button[type="submit"]')
            ->waitForEvent('networkidle')
            ->assertSee('Dashboard')
            ->navigate($settingsUrl, $navigation)
            ->assertValue('input[id="content.store_phone"]', $newPhone)
            ->assertNoJavaScriptErrors();
    } finally {
        $page
            ->fill('input[id="content.store_phone"]', $originalPhone)
            ->press('Save Settings')
            ->assertSee('Settings saved successfully!');
    }
})->group('launch-smoke');
