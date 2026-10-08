<?php

use Database\Seeders\BrowserTestFixtureSeeder;

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://browser-test.kneadit.test');

// The order form's availability, capacity, reorder, coupon and gift card
// fetches read the {data: ...} API envelope. These tests drive the real form
// against the fixture tenant: BrowserTestFixtureSeeder closes one day for the
// whole day (CLOSED_DAY_OFFSET_DAYS ahead of the day it was seeded), leaves the
// other days open, and seeds a coupon and a gift card. Re-run
// tenants:provision-test-tenant if the closed day has drifted out of the
// 30-day window.
//
// assertNoJavaScriptErrors() runs before each submit: it only sees errors from
// the current page, and the submit navigates away.

test('a day the bakery is closed shows the closed message and blocks the order', function () use ($storefrontUrl) {
    $page = visit("{$storefrontUrl}/order");

    waitForOrderFormAvailability($page);
    addLoavesToCart($page, 1);
    fillPickupOrderDetails($page, now()->addDays(BrowserTestFixtureSeeder::CLOSED_DAY_OFFSET_DAYS)->toDateString())
        ->assertSee('The bakery is closed on this day.')
        ->assertDisabled('[data-test="order-form-submit"]')
        ->assertNoJavaScriptErrors();
})->group('launch-smoke');

test('an open day shows no capacity error and the order is placed', function () use ($storefrontUrl) {
    $page = visit("{$storefrontUrl}/order");

    waitForOrderFormAvailability($page);
    addLoavesToCart($page, 1);
    fillPickupOrderDetails($page, now()->addDays(7)->toDateString())
        ->assertDontSee('The bakery is closed on this day.')
        ->assertDontSee('fully booked')
        ->assertEnabled('[data-test="order-form-submit"]')
        ->assertNoJavaScriptErrors()
        ->click('[data-test="order-form-submit"]')
        ->waitForEvent('load')
        ->assertPathBeginsWith('/order/confirmation/');
})->group('launch-smoke');

test('order again loads the previous order into the cart', function () use ($storefrontUrl) {
    $page = visit("{$storefrontUrl}/order");

    waitForOrderFormAvailability($page);
    addLoavesToCart($page, 2);
    fillPickupOrderDetails($page, now()->addDays(7)->toDateString())
        ->click('[data-test="order-form-submit"]')
        ->waitForEvent('load')
        ->assertPathBeginsWith('/order/confirmation/');

    // Placing the order grants this browser session access to it.
    $orderNumber = basename(parse_url($page->url(), PHP_URL_PATH));

    // Explicit timeout: the default 1 second navigation retries and shifts replies (see SettingsPersistenceTest).
    $page->navigate("{$storefrontUrl}/order?reorder={$orderNumber}", ['timeout' => 15_000])
        ->assertSee('2 in cart')
        ->assertSeeIn('[data-test="order-form-subtotal"]', '$60.00')
        ->assertNoJavaScriptErrors();
})->group('launch-smoke');

test('a valid coupon is applied and discounts the total', function () use ($storefrontUrl) {
    $page = visit("{$storefrontUrl}/order");

    addLoavesToCart($page, 1);
    $page->fill('[data-test="order-form-coupon-code"]', BrowserTestFixtureSeeder::COUPON_CODE)
        ->click('[data-test="order-form-coupon-apply"]')
        ->assertSee('10% off applied!')
        ->assertSeeIn('[data-test="order-form-total"]', '$27.00')
        ->assertNoJavaScriptErrors();
})->group('launch-smoke');

test('an unknown coupon is rejected and the total is unchanged', function () use ($storefrontUrl) {
    $page = visit("{$storefrontUrl}/order");

    addLoavesToCart($page, 1);
    $page->fill('[data-test="order-form-coupon-code"]', 'NOSUCHCODE')
        ->click('[data-test="order-form-coupon-apply"]')
        ->assertSee('Coupon not found.')
        ->assertDontSee('applied!')
        ->assertSeeIn('[data-test="order-form-total"]', '$30.00')
        ->assertNoJavaScriptErrors();
})->group('launch-smoke');

test('a valid gift card is applied and covers the total', function () use ($storefrontUrl) {
    $page = visit("{$storefrontUrl}/order");

    addLoavesToCart($page, 1);
    $page->fill('[data-test="order-form-gift-card-code"]', BrowserTestFixtureSeeder::GIFT_CARD_CODE)
        ->click('[data-test="order-form-gift-card-apply"]')
        ->assertSee('Gift card applied! Balance: $50.00')
        ->assertSeeIn('[data-test="order-form-total"]', '$0.00')
        ->assertNoJavaScriptErrors();
})->group('launch-smoke');

test('an unknown gift card shows the error from the response', function () use ($storefrontUrl) {
    $page = visit("{$storefrontUrl}/order");

    addLoavesToCart($page, 1);
    $page->fill('[data-test="order-form-gift-card-code"]', 'NOPE-NOPE-NOPE-NOPE')
        ->click('[data-test="order-form-gift-card-apply"]')
        ->assertSee('Gift card not found.')
        ->assertDontSee('Gift card applied!')
        ->assertNoJavaScriptErrors();
})->group('launch-smoke');
