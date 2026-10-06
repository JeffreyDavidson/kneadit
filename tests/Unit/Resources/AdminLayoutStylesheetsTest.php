<?php

use Symfony\Component\Finder\Finder;

dataset('rebuiltAdminPartials', [
    'layout' => 'css/filament/admin/_layout.css',
    'chrome' => 'css/filament/admin/_chrome.css',
    'forms' => 'css/filament/admin/_forms.css',
]);

/**
 * The partial's CSS with comments removed, so the checks only look at declarations.
 */
function adminPartialDeclarations(string $path): string
{
    return (string) preg_replace('#/\*.*?\*/#s', '', (string) file_get_contents(resource_path($path)));
}

test('the rebuilt admin partials win over Filament without !important', function (string $path) {
    expect(adminPartialDeclarations($path))->not->toContain('!important');
})->with('rebuiltAdminPartials');

test('the rebuilt admin partials use tokens, not hex or rgb colour literals', function (string $path) {
    $css = adminPartialDeclarations($path);

    expect($css)->not->toMatch('/#[0-9a-fA-F]{3,8}\b/')
        ->and($css)->not->toMatch('/\brgba?\(/i')
        ->and($css)->not->toMatch('/\bhsla?\(/i');
})->with('rebuiltAdminPartials');

test('the rebuilt admin partials never set text to white', function (string $path) {
    expect(adminPartialDeclarations($path))->not->toMatch('/color:\s*(white|#fff\w*)(\s|;)/i');
})->with('rebuiltAdminPartials');

test('the rebuilt admin partials read the design system tokens directly', function (string $path) {
    $css = adminPartialDeclarations($path);

    expect($css)->toContain('var(--kn-')
        ->and($css)->not->toMatch('/var\(--(brand|accent-gold|border-subtle|border-medium|hover-bg|active-bg|focus-ring|scrollbar-thumb|thead-bg|status)/');
})->with('rebuiltAdminPartials');

test('every alias variable left in admin-aliases.css is still used by a stylesheet or view', function () {
    $aliases = (string) file_get_contents(resource_path('css/kneadit/admin-aliases.css'));
    preg_match_all('/^\s*(--[a-z0-9-]+):/m', $aliases, $matches);

    $sources = collect(Finder::create()->files()->in([resource_path(), app_path()])->name(['*.css', '*.php']))
        ->reject(fn ($file): bool => $file->getFilename() === 'admin-aliases.css')
        ->map(fn ($file): string => $file->getContents())
        ->push((string) file_get_contents(public_path('css/central-admin.css')))
        ->implode("\n");

    // The --color-* entries are Tailwind theme colours, used as utility classes rather than var().
    $unused = collect($matches[1])
        ->reject(fn (string $alias): bool => str_starts_with($alias, '--color-') || str_contains($sources, "var({$alias}"))
        ->values()
        ->all();

    expect($unused)->toBeEmpty();
});
