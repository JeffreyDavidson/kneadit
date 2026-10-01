<?php

use Database\Seeders\BrowserTestFixtureSeeder;

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://browser-test.kneadit.test');

// The order form posts with fetch, so a rejected order comes back as a 422
// JSON body rather than a page. These tests drive the real form against the
// fixture tenant and tamper with the form through Alpine to make the server
// reject it: a date before the earliest one (the date input's own min would
// otherwise stop the submit in the browser) and a product id that doesn't exist.

function orderFormScript(string $body): string
{
    return <<<JS
        (() => {
            const form = Alpine.\$data(document.querySelector('[data-test="order-form"]'));
            {$body}
        })()
    JS;
}

test('a server field error shows under its input instead of the generic message', function () use ($storefrontUrl) {
    $page = visit("{$storefrontUrl}/order");

    waitForOrderFormAvailability($page);
    addLoavesToCart($page, 1);
    fillPickupOrderDetails($page, now()->subDay()->toDateString());
    $page->script(orderFormScript("form.minDate = '';"));

    $page->assertNoJavaScriptErrors()
        ->click('[data-test="order-form-submit"]')
        ->waitForText('The delivery date field must be a date after or equal to')
        ->assertSeeIn('[data-test="order-form-error-delivery_date"]', 'The delivery date field must be a date after or equal to')
        ->assertSeeIn('[data-test="order-form-submit-error"]', 'The delivery date field must be a date after or equal to')
        ->assertDontSee('There was an error submitting your order');
})->group('launch-smoke');

test('a field error clears when that field changes', function () use ($storefrontUrl) {
    $page = visit("{$storefrontUrl}/order");

    waitForOrderFormAvailability($page);
    addLoavesToCart($page, 1);
    fillPickupOrderDetails($page, now()->subDay()->toDateString());
    $page->script(orderFormScript("form.minDate = '';"));

    $page->click('[data-test="order-form-submit"]')
        ->waitForText('The delivery date field must be a date after or equal to')
        ->fill('[data-test="order-form-delivery-date"]', now()->addDays(7)->toDateString())
        ->assertScript(orderFormScript('return form.fieldErrors.delivery_date === undefined;'))
        ->assertDontSeeIn('[data-test="order-form-error-delivery_date"]', 'The delivery date field must be a date after or equal to');
})->group('launch-smoke');

test('an error on a cart line shows in the cart summary', function () use ($storefrontUrl) {
    $page = visit("{$storefrontUrl}/order");

    waitForOrderFormAvailability($page);
    addLoavesToCart($page, 1);
    fillPickupOrderDetails($page, now()->addDays(7)->toDateString());
    $page->script(orderFormScript('form.cartItems[0].id = 999999;'));

    $page->click('[data-test="order-form-submit"]')
        ->waitForText('The selected items.0.product_id is invalid.')
        ->assertSeeIn('[data-test="order-form-error-items"]', 'The selected items.0.product_id is invalid.')
        ->assertDontSee('There was an error submitting your order');
})->group('launch-smoke');

test('a rejected coupon removes the discount from the total', function () use ($storefrontUrl) {
    $page = visit("{$storefrontUrl}/order");

    addLoavesToCart($page, 1);
    $page->fill('[data-test="order-form-coupon-code"]', BrowserTestFixtureSeeder::COUPON_CODE)
        ->click('[data-test="order-form-coupon-apply"]')
        ->assertSee('10% off applied!')
        ->assertSeeIn('[data-test="order-form-total"]', '$27.00')
        ->fill('[data-test="order-form-coupon-code"]', 'NOSUCHCODE')
        ->click('[data-test="order-form-coupon-apply"]')
        ->waitForText('Coupon not found.')
        ->assertDontSee('applied!')
        ->assertSeeIn('[data-test="order-form-total"]', '$30.00')
        ->assertNoJavaScriptErrors();
})->group('launch-smoke');

test('the order form loads availability once', function () use ($storefrontUrl) {
    $page = visit("{$storefrontUrl}/order");

    waitForOrderFormAvailability($page);
    $page->wait(1)
        ->assertScript("performance.getEntriesByType('resource').filter((entry) => new URL(entry.name).pathname === '/availability').length === 1")
        ->assertNoJavaScriptErrors();
})->group('launch-smoke');
