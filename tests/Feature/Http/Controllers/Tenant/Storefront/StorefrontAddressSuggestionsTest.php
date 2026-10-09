<?php

use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();
    settings(['delivery_enabled' => '1']);
});

test('storefront address boxes suggest addresses when a Google key is set', function (string $routeName, string $dataTest, string $binding) {
    config(['services.google_maps.browser_key' => 'test-browser-key']);

    $response = withoutMiddleware(tenantMiddleware())->get(route($routeName, [], false));

    $response->assertOk()
        ->assertSeeHtml('x-data="addressInput({')
        ->assertSeeHtml("data-test=\"{$dataTest}\"")
        ->assertSeeHtml($binding)
        ->assertSeeHtml('data-address-input');
})->with([
    'checkout delivery address' => ['order.create', 'order-form-delivery-address', 'x-model="form.delivery_address"'],
    'catering venue address' => ['storefront.catering', 'catering-form-venue-address', 'name="venue_address"'],
]);

test('storefront address boxes stay plain without a Google key', function (string $routeName, string $dataTest, string $binding) {
    config(['services.google_maps.browser_key' => null]);

    $response = withoutMiddleware(tenantMiddleware())->get(route($routeName, [], false));

    $response->assertOk()
        ->assertDontSeeHtml('addressInput(')
        ->assertSeeHtml("data-test=\"{$dataTest}\"")
        ->assertSeeHtml($binding);
})->with([
    'checkout delivery address' => ['order.create', 'order-form-delivery-address', 'x-model="form.delivery_address"'],
    'catering venue address' => ['storefront.catering', 'catering-form-venue-address', 'name="venue_address"'],
]);
