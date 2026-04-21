<?php

use Database\Seeders\BrowserTestFixtureSeeder;

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://sweet-surrender.kneadit.test');

// Smoke tests for the main admin resource index pages. Each test logs in as the
// seeded fixture admin, clicks the sidebar nav link for the resource, and
// asserts the resource index renders with the resource's heading visible and
// no JavaScript errors.
//
// Scope note: the Pest browser plugin creates a fresh browser context per
// visit() call, so cookies from a previous test's login are not preserved.
// Each test runs the full login dance. Per-test cost ~4-5s; acceptable for a
// ~5-test smoke suite.
//
// Prerequisite: BrowserTestFixtureSeeder has been run against the target tenant.

/**
 * @return list<array{0: string, 1: string}>
 */
dataset('resource_index_pages', [
    'products' => ['products', 'Products'],
    'orders' => ['orders', 'Orders'],
    'customers' => ['customers', 'Customers'],
    'categories' => ['categories', 'Categories'],
    'blog-posts' => ['blog-posts', 'Blog Posts'],
]);

test('admin resource index page renders cleanly', function (string $slug, string $heading) use ($storefrontUrl) {
    visit("{$storefrontUrl}/admin/login")
        ->fill('input[type="email"]', BrowserTestFixtureSeeder::ADMIN_EMAIL)
        ->fill('input[type="password"]', BrowserTestFixtureSeeder::ADMIN_PASSWORD)
        ->click('button[type="submit"]')
        ->wait(6)
        ->click("a.fi-sidebar-item-btn[href\$=\"/admin/{$slug}\"]")
        ->wait(3)
        ->assertSee($heading)
        ->assertNoJavaScriptErrors();
})->with('resource_index_pages');
