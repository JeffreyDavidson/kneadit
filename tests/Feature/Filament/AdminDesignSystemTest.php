<?php

use App\Models\Platform\Tenant;
use Filament\Enums\ThemeMode;
use Filament\Facades\Filament;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\get;

dataset('designSystemPanels', ['admin', 'central']);

dataset('designSystemColors', [
    'primary' => ['primary', '#d4920c'],
    'danger' => ['danger', '#a83248'],
    'success' => ['success', '#4f6e4f'],
    'warning' => ['warning', '#8a5a0a'],
    'info' => ['info', '#2f5f7a'],
]);

dataset('removedThemePickerFiles', [
    'shared palettes' => 'Filament/Shared/PanelThemes.php',
    'bakery admin page' => 'Filament/Pages/Settings/AdminAppearance.php',
    'platform admin page' => 'Filament/Central/Pages/Appearance.php',
]);

dataset('removedThemePickerRoutes', [
    'bakery admin page' => 'filament.admin.pages.admin-appearance',
    'platform admin page' => 'filament.central.pages.appearance',
]);

test('the panel uses Instrument Sans for body text and Young Serif as its display face', function (string $panel) {
    $panel = Filament::getPanel($panel);

    expect($panel->getFontFamily())->toBe('Instrument Sans')
        ->and($panel->getSerifFontFamily())->toBe('Young Serif');
})->with('designSystemPanels');

test('the panel keeps dark mode forced until the admin partials are rebuilt', function (string $panel) {
    $panel = Filament::getPanel($panel);

    expect($panel->hasDarkMode())->toBeTrue()
        ->and($panel->hasDarkModeForced())->toBeTrue()
        ->and($panel->getDefaultThemeMode())->toBe(ThemeMode::Dark);
})->with('designSystemPanels');

test('the panel colour palettes are generated from the design system tokens', function (string $panel, string $name, string $hex) {
    expect(Filament::getPanel($panel)->getColors()[$name])->toBe(Color::hex($hex));
})->with('designSystemPanels')->with('designSystemColors');

test('the gray palette keeps the hue of the brown source colour but stays a muted neutral', function (string $panel) {
    $gray = Filament::getPanel($panel)->getColors()['gray'];
    [, $sourceChroma, $sourceHue] = sscanf(Color::convertToOklch('#7a5a3a'), 'oklch(%f %f %f)');
    [, $chroma, $hue] = sscanf($gray[500], 'oklch(%f %f %f)');

    expect($gray)->toHaveCount(11)
        ->and($hue)->toEqualWithDelta($sourceHue, 0.01)
        ->and($chroma)->toEqualWithDelta($sourceChroma, 0.001)
        ->and($chroma)->toBeLessThan(0.1);
})->with('designSystemPanels');

test('the theme picker classes no longer exist', function (string $file) {
    expect(file_exists(app_path($file)))->toBeFalse();
})->with('removedThemePickerFiles');

test('the theme picker pages are not registered', function (string $route) {
    expect(Route::has($route))->toBeFalse();
})->with('removedThemePickerRoutes');

test('the central theme setting is gone from config', function () {
    expect(config('kneadit'))->not->toHaveKey('central_theme');
});

test('the bakery admin renders without the old runtime theme styles', function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['app.kneadit.test'], 'tenancy.tenant_domain' => 'kneadit.test']);
    $tenant = Tenant::factory()->create(['id' => 'designbakery']);
    $tenant->domains()->create(['domain' => 'designbakery.kneadit.test']);

    $response = get('https://designbakery.kneadit.test/admin/login');

    $response->assertOk()
        ->assertDontSeeHtml('--accent-gold:#d4a574')
        ->assertDontSeeHtml('--brand-950:#1c1410');
});

test('the platform admin renders without the old runtime theme styles', function () {
    setUpCentralTest();

    $response = get('https://app.getkneadit.test/admin/login');

    $response->assertOk()
        ->assertDontSeeHtml('--platform-950:#0c0a09')
        ->assertDontSeeHtml('--color-warm-black:var(--platform-900)');
});

test('the bakery admin favicon links point at files that exist', function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['app.kneadit.test'], 'tenancy.tenant_domain' => 'kneadit.test']);
    $tenant = Tenant::factory()->create(['id' => 'faviconbakery']);
    $tenant->domains()->create(['domain' => 'faviconbakery.kneadit.test']);

    $html = get('https://faviconbakery.kneadit.test/admin/login')->assertOk()->getContent();
    preg_match_all('/<link rel="(?:icon|apple-touch-icon)"[^>]*href="([^"]+)"/', (string) $html, $matches);

    $paths = collect($matches[1])->map(fn (string $href): string => public_path(ltrim((string) parse_url($href, PHP_URL_PATH), '/')));

    expect($paths)->not->toBeEmpty()
        ->and($paths->filter(fn (string $path): bool => str_contains($path, 'favicons/')))->toHaveCount(3)
        ->and($paths->reject(fn (string $path): bool => file_exists($path)))->toBeEmpty();
});
