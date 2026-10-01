<?php

use Database\Seeders\BrowserTestFixtureSeeder;

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://browser-test.kneadit.test');

// Places a delivery order through the real storefront form. The fixture tenant
// has no Stripe account, so a successful submit lands on the confirmation page.
// A rejected submit (for example "The selected delivery tier is invalid.")
// redirects back to /order, so the confirmation path is the success signal.
//
// The delivery tier <option> values are zero-based positions in the tenant's
// delivery_fee_tiers setting, so the second distance option has the value "1".
//
// assertNoJavaScriptErrors() runs before each submit: it only sees errors from
// the current page, and the submit navigates away.

function addDeliveryProductToCart(mixed $page, int $quantity): void
{
    $increment = sprintf(
        '[data-product-name="%s"] [data-test="order-form-product-increment"]',
        BrowserTestFixtureSeeder::DELIVERY_PRODUCT_NAME,
    );

    foreach (range(1, $quantity) as $ignored) {
        $page->click($increment);
    }
}

function fillDeliveryOrderDetails(mixed $page, string $tierValue): mixed
{
    return $page
        ->click('[data-test="order-form-delivery-type-delivery"]')
        ->fill('[data-test="order-form-delivery-address"]', '123 Main Street, Davenport, FL 33837')
        ->select('[data-test="order-form-delivery-tier"]', $tierValue)
        ->fill('[data-test="order-form-delivery-date"]', now()->addDays(7)->toDateString())
        ->fill('[data-test="order-form-customer-name"]', 'Delivery Tester')
        ->fill('[data-test="order-form-customer-email"]', 'delivery-tester@example.com')
        ->fill('[data-test="order-form-customer-phone"]', '555-0123');
}

test('a delivery order on the second distance tier shows that tier fee and is placed', function () use ($storefrontUrl) {
    $page = visit("{$storefrontUrl}/order");

    waitForOrderFormAvailability($page);
    addDeliveryProductToCart($page, 1);
    fillDeliveryOrderDetails($page, '1')
        ->assertSeeIn('[data-test="order-form-delivery-fee"]', '$12.00')
        ->assertSeeIn('[data-test="order-form-total"]', '$42.00')
        ->assertNoJavaScriptErrors()
        ->click('[data-test="order-form-submit"]')
        ->waitForEvent('load')
        ->assertPathBeginsWith('/order/confirmation/')
        ->assertDontSee('The selected delivery tier is invalid')
        ->assertSee('Delivery Fee')
        ->assertSee('$12.00');
})->group('launch-smoke');

test('a delivery order at the free-delivery minimum has no delivery fee and is placed', function () use ($storefrontUrl) {
    $page = visit("{$storefrontUrl}/order");

    waitForOrderFormAvailability($page);
    addDeliveryProductToCart($page, 2);
    fillDeliveryOrderDetails($page, '1')
        ->assertSeeIn('[data-test="order-form-subtotal"]', '$60.00')
        ->assertMissing('[data-test="order-form-delivery-fee"]')
        ->assertSeeIn('[data-test="order-form-total"]', '$60.00')
        ->assertNoJavaScriptErrors()
        ->click('[data-test="order-form-submit"]')
        ->waitForEvent('load')
        ->assertPathBeginsWith('/order/confirmation/')
        ->assertDontSee('Delivery Fee');
})->group('launch-smoke');
