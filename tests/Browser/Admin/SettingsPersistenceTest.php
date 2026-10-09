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
//
// The expectations wait up to 15 seconds as well (the default is 5): signing in
// and out are full round trips to a single-threaded server. When a step does
// fail, the restore at the end first goes back to the settings page, since the
// failure can leave the browser anywhere (on the login page, for one), and
// signs in again if the session was lost, so it neither fails on the wrong page
// and hides the real error nor leaves the changed phone behind.

test('a saved Store Phone survives a reload and signing out and back in', function () use ($storefrontUrl) {
    // Typed and shown in the US national format; stored in E.164.
    $newPhone = '(913) 555-01'.random_int(10, 99);
    $settingsUrl = "{$storefrontUrl}/admin/manage-settings";
    $navigation = ['timeout' => 15_000];

    withBrowserTimeout(15_000, function () use ($storefrontUrl, $newPhone, $settingsUrl, $navigation): void {
        $signIn = fn (mixed $page): mixed => $page
            ->fill('input[type="email"]', BrowserTestFixtureSeeder::ADMIN_EMAIL)
            ->fill('input[type="password"]', BrowserTestFixtureSeeder::ADMIN_PASSWORD)
            ->click('button[type="submit"]')
            ->waitForEvent('networkidle')
            ->assertSee('Dashboard');

        $page = $signIn(visit("{$storefrontUrl}/admin/login"))
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
                ->assertSee('Sign out');

            // Clicked once, with its own timeout, instead of through press():
            // Sign out is a form post that navigates, and Playwright waits for
            // that navigation to start before the click returns. press() gives
            // each try 1 second, so a slow response made the click report a
            // timeout after it had already signed out, and the retry then
            // looked for the Sign out button on the login page.
            $page->page()
                ->locator('button:has-text("Sign out")')
                ->click(['timeout' => 15_000]);

            $page
                ->waitForEvent('networkidle')
                ->assertPathIs('/admin/login');

            $signIn($page)
                ->navigate($settingsUrl, $navigation)
                ->assertValue('input[id="content.store_phone"]', $newPhone)
                ->assertNoJavaScriptErrors();
        } finally {
            $page->navigate($settingsUrl, $navigation);

            if ($page->script('location.pathname') === '/admin/login') {
                $signIn($page)->navigate($settingsUrl, $navigation);
            }

            $page
                ->fill('input[id="content.store_phone"]', $originalPhone)
                ->press('Save Settings')
                ->assertSee('Settings saved successfully!');
        }
    });
})->group('launch-smoke');
