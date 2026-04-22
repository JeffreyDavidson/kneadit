<?php

beforeEach(function () {
    setUpCentralTest();
});

test('storefront layout has pwa install prompt', function () {
    $layout = file_get_contents(resource_path('views/components/layouts/storefront.blade.php'));

    expect($layout)->toContain('pwaInstall')->toContain('beforeinstallprompt')->toContain('pwaInstallBtn');
});

test('pwa prompt hidden by default', function () {
    $layout = file_get_contents(resource_path('views/components/layouts/storefront.blade.php'));

    expect($layout)->toMatch('/id="pwaInstall"\s+class="hidden /');
});

test('pwa prompt has dismiss functionality', function () {
    $layout = file_get_contents(resource_path('views/components/layouts/storefront.blade.php'));

    expect($layout)->toContain('dismissPwa')->toContain('pwaDismissed');
});

test('manifest link exists in storefront', function () {
    $layout = file_get_contents(resource_path('views/components/layouts/storefront.blade.php'));

    expect($layout)->toContain('rel="manifest"');
});
