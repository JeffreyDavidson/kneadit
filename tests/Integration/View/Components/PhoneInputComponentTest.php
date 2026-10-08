<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('renders the shared phone input for a classic form post', function () {
    $html = Blade::render('<x-phone-input id="profile-phone" name="phone" value="+19133877359" input-class="input-field" data-test="profile-phone" />');

    expect($html)
        ->toContain('x-data="phoneInput({ value: \'+19133877359\', country: \'us\' })"')
        ->toContain('x-modelable="value"')
        ->toContain('type="tel"')
        ->toContain('id="profile-phone"')
        ->toContain('data-phone-input')
        ->toContain('data-test="profile-phone"')
        ->toContain('class="input-field"')
        ->toContain('name="phone"')
        ->toContain('x-bind:name="false"')
        ->toContain('<input type="hidden" x-bind:name="\'phone\'" x-bind:value="value" />');
});

test('passes x-model to the x-modelable root for an Alpine form and sends no hidden input', function () {
    $html = Blade::render('<x-phone-input id="order-customer-phone" x-model="form.customer_phone" input-class="order-input" />');

    expect($html)
        ->toContain('x-model="form.customer_phone"')
        ->not->toContain('type="hidden"')
        ->not->toContain('name="');
});

test('starts the country selector on the bakery country', function () {
    settings(['store_phone' => '+442079460958']);

    $html = Blade::render('<x-phone-input id="phone" />');

    expect($html)->toContain("country: 'gb'");
});
