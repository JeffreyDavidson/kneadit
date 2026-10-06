<?php

dataset('themedAdminViews', [
    'tenant admin card' => 'views/components/tenant-admin/card.blade.php',
    'onboarding page' => 'views/filament/pages/platform/onboarding.blade.php',
    'dashboard settings link' => 'views/filament/render-hooks/dashboard-settings-link.blade.php',
]);

test('the themed admin views use no fixed white fill or text', function (string $path) {
    expect((string) file_get_contents(resource_path($path)))
        ->not->toMatch('/\b(bg|text)-white\b/')
        ->not->toMatch('/color:\s*white/i');
})->with('themedAdminViews');

test('the themed admin views hard-code no hex or rgb colours', function (string $path) {
    expect((string) file_get_contents(resource_path($path)))
        ->not->toMatch('/#[0-9a-fA-F]{3,8}\b/')
        ->not->toMatch('/\brgba?\(/i');
})->with('themedAdminViews');
