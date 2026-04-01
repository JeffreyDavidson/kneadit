<?php

use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
});

test('show returns the contact view with tenant settings', function () {
    $response = $this
        ->withoutMiddleware(tenantMiddleware())
        ->get(route('contact.show'));

    $response->assertOk()
        ->assertViewIs('storefront.contact')
        ->assertViewHas('settings', fn (TenantSettings $s) => $s->storeName === 'Our Bakery');
});
