<?php

$centralUrl = env('BROWSER_TEST_CENTRAL_URL', 'http://kneadit.test');

// Smoke tests for the landlord (central) admin panel — resource indexes and
// platform pages. Each test loads a pre-authenticated browser context via
// authenticatedCentralVisit() and asserts the h1 heading + no JS errors.
//
// /admin/blog-posts excluded: pre-existing misconfiguration — the central
// resource queries a blog_posts table that doesn't exist on the central DB
// (blog posts are tenant-scoped).
//
// Prerequisites:
//   1. php artisan db:seed --class="Database\\Seeders\\BrowserTestCentralFixtureSeeder"
//   2. python3 tests/Browser/Helpers/prepare-admin-session.py

/**
 * @return list<array{0: string, 1: string}>
 */
dataset('central_admin_pages', [
    // Resources
    'tenants' => ['tenants', 'Tenants'],
    'support-tickets' => ['support-tickets', 'Support Tickets'],
    'email-campaigns' => ['email-campaigns', 'Email Campaigns'],
    'messages' => ['messages', 'Messages'],
    'announcements' => ['announcements', 'Platform Announcements'],
    'scheduled-checkins' => ['scheduled-checkins', 'Scheduled Checkins'],

    // Platform pages
    'activity' => ['activity', 'Activity'],
    'analytics' => ['analytics', 'Analytics'],
    'bakery-insights' => ['bakery-insights', 'Bakery Insights'],
    'tenant-comparison' => ['tenant-comparison', 'Bakery Comparison'],
    'feature-usage' => ['feature-usage', 'Feature Usage'],
    'data-export' => ['data-export', 'Data Export'],
    'maintenance-mode' => ['maintenance-mode', 'Maintenance Mode'],
    'onboarding-tracker' => ['onboarding-tracker', 'Onboarding Tracker'],
]);

test('central admin page renders cleanly', function (string $slug, string $heading) use ($centralUrl) {
    authenticatedCentralVisit("{$centralUrl}/admin/{$slug}")
        ->assertSee($heading)
        ->assertNoJavaScriptErrors();
})->with('central_admin_pages');
