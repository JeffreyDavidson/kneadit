<?php

use App\Http\Controllers\Storefront\ManifestController;

test('it uses brandColorPrimary from TenantSettings for theme_color', function () {
    $settings = makeTenantSettings(
        store: makeStoreInfo(['name' => 'Test Bakery']),
        branding: makeBrandingSettings([
            'brandColorPrimary' => '#ff5500',
            'businessTagline' => 'Fresh daily',
        ]),
    );

    $controller = new ManifestController;
    $response = $controller($settings);
    $data = $response->getData(true);

    expect($data['theme_color'])->toBe('#ff5500')
        ->and($data['name'])->toBe('Test Bakery');
});
