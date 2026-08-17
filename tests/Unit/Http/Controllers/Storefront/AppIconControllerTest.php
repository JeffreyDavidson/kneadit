<?php

use App\Http\Controllers\Storefront\AppIconController;
use App\Services\Support\AppIconGeneratorService;

test('it generates icon using brandColorPrimary from TenantSettings', function () {
    $settings = makeTenantSettings(
        store: makeStoreInfo(['name' => 'Test']),
        branding: makeBrandingSettings(['brandColorPrimary' => '#ff5500']),
    );

    $controller = new AppIconController;
    $response = $controller('192', $settings, new AppIconGeneratorService);

    // Verify the image background uses the settings color (#ff5500 = rgb(255, 85, 0))
    $content = $response->getContent();

    if (! is_string($content)) {
        throw new RuntimeException('Expected the icon response to contain image data.');
    }

    $img = imagecreatefromstring($content);

    if (! $img instanceof GdImage) {
        throw new RuntimeException('Expected valid image data.');
    }
    $rgb = imagecolorat($img, 0, 0);
    $r = ($rgb >> 16) & 0xFF;
    $g = ($rgb >> 8) & 0xFF;
    $b = $rgb & 0xFF;
    imagedestroy($img);

    expect($r)->toBe(255)
        ->and($g)->toBe(85)
        ->and($b)->toBe(0);
});
