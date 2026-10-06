<?php

use Symfony\Component\Finder\Finder;

/**
 * Bakery admin views are readable in light and dark, so they use token classes
 * instead of fixed colours. Left out on purpose: the platform (central) views,
 * which are forced dark; the printable documents (menu and labels), which are
 * paper in fixed colours; the storefront theme previews; and the dashboard
 * widget preview.
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
        ])
        ->name('*.blade.php')
        ->notPath(['printable-menu', 'label-generator', 'onboarding-preview', 'theme-selector', 'widget-preview']);

    $files = ['render-hooks/dashboard-settings-link.blade.php' => "{$viewsPath}/filament/render-hooks/dashboard-settings-link.blade.php"];

    foreach ($views as $view) {
        $files[$view->getRelativePathname()] = $view->getPathname();
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

test('the themed admin views use no fixed white fill or text', function (string $path) {
    expect((string) file_get_contents($path))
        ->not->toMatch('/(?<![:\w-])(bg|text)-white(?![\w-])/')
        ->not->toMatch('/color:\s*white/i');
})->with('themedAdminViews');

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
})->with('themedAdminViews');

test('solid status buttons in the admin chrome use the token colours', function () {
    $css = (string) file_get_contents(dirname(__DIR__, 3).'/resources/css/filament/admin/_chrome.css');

    expect($css)
        ->toContain('.fi-btn.fi-color-success')
        ->toContain('.fi-btn.fi-color-warning')
        ->toContain('.fi-btn.fi-color-info')
        ->toContain('var(--kn-on-danger)');
});
