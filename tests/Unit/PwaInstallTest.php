<?php

beforeEach(function () {
    setUpCentralTest();
});

test('storefront pwa component has install prompt', function () {
    $pwaPrompt = file_get_contents(resource_path('views/components/storefront/pwa-install-prompt.blade.php'));

    expect($pwaPrompt)->toContain('pwaInstall')->toContain('beforeinstallprompt')->toContain('pwaInstallBtn');
});

test('pwa prompt component is hidden by default', function () {
    $pwaPrompt = file_get_contents(resource_path('views/components/storefront/pwa-install-prompt.blade.php'));

    expect($pwaPrompt)->toMatch('/id="pwaInstall"[^>]*class="[^"]*\bhidden\b/');
});

test('pwa prompt component has dismiss functionality', function () {
    $pwaPrompt = file_get_contents(resource_path('views/components/storefront/pwa-install-prompt.blade.php'));

    expect($pwaPrompt)->toContain('dismissPwa')->toContain('pwaDismissed');
});

test('manifest link exists in storefront', function () {
    $layout = file_get_contents(resource_path('views/components/layouts/storefront.blade.php'));

    expect($layout)->toContain('rel="manifest"');
});
