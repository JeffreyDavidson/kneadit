<?php

use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    app()->instance(TenantSettings::class, makeTenantSettings(store: makeStoreInfo(['name' => 'Sunrise Bakery'])));
});

test('the admin brand logo is labelled with the bakery name', function () {
    $html = view('filament.brand-logo')->render();

    expect($html)->toContain('alt="Sunrise Bakery"');
});

test('the tenant 404 page names the bakery', function () {
    $html = view('errors.404')->render();

    expect($html)->toContain('<title>Page Not Found | Sunrise Bakery</title>');
});
