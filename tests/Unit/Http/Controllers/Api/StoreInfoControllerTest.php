<?php

use App\Http\Controllers\Api\StoreInfoController;

test('it returns store info from TenantSettings', function () {
    $settings = makeTenantSettings(
        store: makeStoreInfo([
            'name' => 'Sweet Crumbs',
            'email' => 'hello@sweetcrumbs.com',
            'phone' => '555-1234',
            'address' => '42 Baker St',
            'website' => 'https://sweetcrumbs.com',
            'logo' => 'logos/sweet.png',
            'tagline' => 'Baked with love',
        ]),
        branding: makeBrandingSettings(['brandColorPrimary' => '#e84393']),
        homepage: makeHomepageSettings([
            'operatingHours' => ['mon' => '8am-5pm'],
            'socialMediaLinks' => ['facebook' => 'https://fb.com/sweet', 'instagram' => 'https://ig.com/sweet'],
        ]),
    );

    $controller = new StoreInfoController;
    $resource = $controller($settings);
    $attributes = $resource->toAttributes(request());

    expect($attributes['store_name'])->toBe('Sweet Crumbs')
        ->and($attributes['email'])->toBe('hello@sweetcrumbs.com')
        ->and($attributes['phone'])->toBe('555-1234')
        ->and($attributes['address'])->toBe('42 Baker St');
});
