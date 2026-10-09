<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('renders the address suggestions component for a classic form post when a Google key is set', function () {
    config(['services.google_maps.browser_key' => 'test-browser-key']);

    $html = Blade::render('<x-address-input id="catering-venue-address" name="venue_address" value="123 Wedding Ln" rows="2" input-class="input-field" placeholder="Where?" data-test="catering-form-venue-address" />');

    expect($html)
        ->toContain('x-data="addressInput({ value: \'123 Wedding Ln\', key: \'test-browser-key\', country: \'us\' })"')
        ->toContain('x-modelable="value"')
        ->toContain('<textarea')
        ->toContain('rows="2"')
        ->toContain('id="catering-venue-address"')
        ->toContain('name="venue_address"')
        ->toContain('x-model="value"')
        ->toContain('role="combobox"')
        ->toContain('data-address-input')
        ->toContain('data-test="catering-form-venue-address"')
        ->toContain('class="input-field"')
        ->toContain('placeholder="Where?"')
        ->toContain('role="listbox"')
        ->toContain('Google Maps');
});

test('passes x-model to the x-modelable root for an Alpine form', function () {
    config(['services.google_maps.browser_key' => 'test-browser-key']);

    $html = Blade::render('<x-address-input id="order-delivery-address" x-model="form.delivery_address" input-class="order-input" />');

    expect($html)
        ->toContain('x-model="form.delivery_address"')
        ->toContain('x-modelable="value"')
        ->toContain('type="text"')
        ->not->toContain('name="');
});

test('limits suggestions to the bakery country', function () {
    config(['services.google_maps.browser_key' => 'test-browser-key']);
    settings(['store_phone' => '+442079460958']);

    $html = Blade::render('<x-address-input id="address" />');

    expect($html)->toContain("country: 'gb'");
});

test('renders a plain text box without Google when no key is set', function () {
    config(['services.google_maps.browser_key' => null]);

    $html = Blade::render('<x-address-input id="order-delivery-address" x-model="form.delivery_address" rows="3" input-class="order-input" data-test="order-form-delivery-address" />');

    expect($html)
        ->toContain('<textarea')
        ->toContain('x-model="form.delivery_address"')
        ->toContain('id="order-delivery-address"')
        ->toContain('data-test="order-form-delivery-address"')
        ->toContain('class="order-input"')
        ->not->toContain('addressInput(')
        ->not->toContain('data-address-input')
        ->not->toContain('Google Maps');
});
