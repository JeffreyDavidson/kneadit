<?php

use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    app()->instance(TenantSettings::class, makeTenantSettings(store: makeStoreInfo(['name' => 'Sunrise Bakery'])));
});

test('the admin brand logo is labelled with the bakery name', function () {
    $html = view('filament.brand-logo')->render();

    expect($html)->toContain('alt="Sunrise Bakery"');
});

test('the admin brand logo shows the bakery logo with size constraints and the bakery name', function () {
    Storage::fake('public');
    Storage::disk('public')->put('logos/wide.png', 'image');
    app()->instance(TenantSettings::class, makeTenantSettings(store: makeStoreInfo([
        'name' => 'Sunrise Bakery',
        'logo' => 'logos/wide.png',
    ])));

    $html = view('filament.brand-logo')->render();

    expect($html)
        ->toContain('class="kn-brand-logo"')
        ->toContain('storage/logos/wide.png')
        ->toContain('alt=""')
        ->toContain('class="kn-brand-name">Sunrise Bakery</span>')
        ->not->toContain('logo-transparent.png');
});

test('the admin brand logo falls back to the KneadIt wordmark when the bakery has no logo', function () {
    $html = view('filament.brand-logo')->render();

    expect($html)
        ->toContain('logo-transparent.png')
        ->toContain('alt="Sunrise Bakery"')
        ->not->toContain('kn-brand-name');
});

test('the tenant 404 page names the bakery', function () {
    $html = view('errors.404')->render();

    expect($html)->toContain('<title>Page Not Found | Sunrise Bakery</title>');
});
