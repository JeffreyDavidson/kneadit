<?php

beforeEach(function () {
    setUpCentralTest();
});

test('storefront layout has pwa install prompt', function () {
    $layout = file_get_contents(resource_path('views/layouts/storefront.blade.php'));

    expect($layout)->toContain('pwaInstall');
    expect($layout)->toContain('beforeinstallprompt');
    expect($layout)->toContain('pwaInstallBtn');
});

test('pwa prompt hidden by default', function () {
    $layout = file_get_contents(resource_path('views/layouts/storefront.blade.php'));

    expect($layout)->toContain('id="pwaInstall" style="display:none');
});

test('pwa prompt has dismiss functionality', function () {
    $layout = file_get_contents(resource_path('views/layouts/storefront.blade.php'));

    expect($layout)->toContain('dismissPwa');
    expect($layout)->toContain('pwaDismissed');
});

test('manifest link exists in storefront', function () {
    $layout = file_get_contents(resource_path('views/layouts/storefront.blade.php'));

    expect($layout)->toContain('rel="manifest"');
});
