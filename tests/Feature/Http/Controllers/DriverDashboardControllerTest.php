<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('driver dashboard returns ok with settings', function () {
    $response = $this
        ->withoutMiddleware(tenantMiddleware())
        ->get(route('driver.index'));

    $response->assertOk()
        ->assertViewIs('storefront.driver')
        ->assertViewHas('settings');
});
