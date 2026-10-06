<?php

use Symfony\Component\Finder\Finder;

/**
 * Bakery admin and platform (central) admin views are readable in light and
 * dark, so they use token classes instead of fixed colours. Left out on
 * purpose: the printable documents (menu and labels), which are paper in fixed
 * colours; the storefront theme previews; and the dashboard widget preview.
 * The platform previews of customer-facing output (emails, the announcement
 * banner and the search result) keep their own fixed colours and are exempt
 * from the fixed-white and hex checks.
 *
 * @return array<string, string> Relative path => absolute path.
 */
function themedAdminViewFiles(): array
{
    $viewsPath = dirname(__DIR__, 3).'/resources/views';

    $views = Finder::create()
        ->files()
        ->in([
            "{$viewsPath}/filament/pages",
            "{$viewsPath}/filament/resources",
            "{$viewsPath}/filament/widgets",
            "{$viewsPath}/components/tenant-admin",
            "{$viewsPath}/components/filament",
            "{$viewsPath}/filament/central",
            "{$viewsPath}/components/central",
        ])
        ->name('*.blade.php')
        ->notPath(['printable-menu', 'label-generator', 'onboarding-preview', 'theme-selector', 'widget-preview', 'marketing']);

    $files = ['filament/render-hooks/dashboard-settings-link.blade.php' => "{$viewsPath}/filament/render-hooks/dashboard-settings-link.blade.php"];

    foreach ($views as $view) {
        $files[str_replace("{$viewsPath}/", '', $view->getPathname())] = $view->getPathname();
    }

    return $files;
}

/**
 * The view's markup without its <style> blocks, where print stylesheets have to name literal colours.
 */
function adminViewWithoutStyleBlocks(string $path): string
{
    return (string) preg_replace('#<style\b.*?</style>#s', '', (string) file_get_contents($path));
}

dataset('themedAdminViews', fn (): array => themedAdminViewFiles());

dataset('themedCentralViews', fn (): array => array_filter(
    themedAdminViewFiles(),
    fn (string $key): bool => str_contains($key, 'central/') && ! str_contains($key, 'central/partials/'),
    ARRAY_FILTER_USE_KEY,
));

/**
 * Every themed view except the platform previews of customer-facing output, which are drawn in the fixed colours the customer sees.
 */
dataset('themedAdminViewsWithoutPreviews', fn (): array => array_filter(
    themedAdminViewFiles(),
    fn (string $path): bool => ! str_contains($path, '/filament/central/partials/'),
    ARRAY_FILTER_USE_BOTH,
));

test('the themed admin views use no fixed white fill or text', function (string $path) {
    expect((string) file_get_contents($path))
        ->not->toMatch('/(?<![:\w-])(bg|text)-white(?![\w-])/')
        ->not->toMatch('/color:\s*white/i');
})->with('themedAdminViewsWithoutPreviews');

test('the themed admin views do not use the dark-only brand ladder as a fill or heading colour', function (string $path) {
    expect((string) file_get_contents($path))
        ->not->toMatch('/(?<![:\w-])(bg-brand-(50|100|800|900)|text-brand-900)(?![\w\/-])/');
})->with('themedAdminViews');

test('the themed admin views build no Tailwind colour class from a variable', function (string $path) {
    expect((string) file_get_contents($path))
        ->not->toMatch('/(?<![:\w-])(bg|text|border)-\{\{/');
})->with('themedAdminViews');

test('the themed admin views hard-code no hex or rgb colours outside print stylesheets', function (string $path) {
    expect(adminViewWithoutStyleBlocks($path))
        ->not->toMatch('/#[0-9a-fA-F]{6}\b/')
        ->not->toMatch('/\brgba?\(/i');
})->with('themedAdminViewsWithoutPreviews');

test('the themed admin views use none of the dark-only platform colour names', function (string $path) {
    expect((string) file_get_contents($path))
        ->not->toMatch('/(?<![\w-])(bg|text|border|from|to|via|divide|stroke|fill|ring)-(honey|warm-black|espresso|cinnamon|golden|butter|parchment)(?![\w-])/')
        ->not->toMatch('/(?<![\w-])(bg|text|border|from|to|via|divide|stroke|fill|ring)-(red|green|emerald|amber|yellow|sky|blue|slate|gray|black)-\d{2,3}/')
        ->not->toMatch('/var\(--(platform|accent|border-subtle|border-medium|hover-bg|active-bg|thead-bg)/');
})->with('themedCentralViews');

test('solid status buttons in the admin chrome use the token colours', function () {
    $css = (string) file_get_contents(dirname(__DIR__, 3).'/resources/css/filament/admin/_chrome.css');

    expect($css)
        ->toContain('.fi-btn.fi-color-success')
        ->toContain('.fi-btn.fi-color-warning')
        ->toContain('.fi-btn.fi-color-info')
        ->toContain('var(--kn-on-danger)');
});
