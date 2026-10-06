<?php

/**
 * @return array{light: string, dark: string}
 */
function kneaditTokenBlocks(): array
{
    $css = (string) file_get_contents(resource_path('css/kneadit/tokens.css'));

    preg_match('/:root\s*\{(.*?)\}/s', $css, $light);
    preg_match('/\.dark\s*\{(.*?)\}/s', $css, $dark);

    return [
        'light' => $light[1] ?? '',
        'dark' => $dark[1] ?? '',
    ];
}

dataset('kneaditColourTokens', [
    'bg', 'surface', 'surface-sunken', 'surface-hover', 'ink', 'ink-2', 'muted',
    'border', 'border-control', 'honey', 'honey-hover', 'on-honey', 'honey-text',
    'espresso', 'on-espresso', 'danger', 'on-danger', 'danger-tint', 'success',
    'success-tint', 'warning', 'warning-tint', 'info', 'info-tint',
    'shadow-card', 'shadow-pop', 'shadow-focus',
]);

dataset('kneaditStaticTokens', [
    'font-display', 'font-sans', 'font-mono',
    'space-1', 'space-2', 'space-3', 'space-4', 'space-6', 'space-8', 'space-12',
    'radius-sm', 'radius-md', 'radius-lg', 'radius-xl', 'radius-pill',
]);

dataset('legacyAdminVariables', [
    '--brand-50', '--brand-100', '--brand-150', '--brand-200', '--brand-300', '--brand-400',
    '--brand-500', '--brand-600', '--brand-700', '--brand-800', '--brand-900',
    '--border-subtle',
]);

test('every colour and shadow token is defined for light on :root and for dark under .dark', function (string $token) {
    $blocks = kneaditTokenBlocks();

    expect($blocks['light'])->toContain("--kn-{$token}:")
        ->and($blocks['dark'])->toContain("--kn-{$token}:");
})->with('kneaditColourTokens');

test('every font, spacing and radius token is defined on :root', function (string $token) {
    expect(kneaditTokenBlocks()['light'])->toContain("--kn-{$token}:");
})->with('kneaditStaticTokens');

test('the display font stack is Young Serif and the body font stack is Instrument Sans', function () {
    $light = kneaditTokenBlocks()['light'];

    expect($light)->toContain('"Young Serif"')
        ->and($light)->toContain('"Instrument Sans"');
});

test('the on-honey text colour is the warm brown from the design system in both themes', function () {
    $blocks = kneaditTokenBlocks();

    expect($blocks['light'])->toContain('--kn-on-honey: #1c1410;')
        ->and($blocks['dark'])->toContain('--kn-on-honey: #1c1410;');
});

test('the legacy admin variables are aliased to design system tokens', function (string $variable) {
    $css = (string) file_get_contents(resource_path('css/kneadit/admin-aliases.css'));

    expect($css)->toMatch('/'.preg_quote($variable, '/').':\s*[^;]*var\(--kn-/');
})->with('legacyAdminVariables');

test('the platform admin theme defines no variable aliases of its own', function () {
    $css = (string) file_get_contents(resource_path('css/filament/central/theme.css'));

    expect($css)->not->toMatch('/^\s*--[a-z0-9-]+:/m');
});

test('both panel themes import the legacy aliases the widget preview reads', function (string $theme) {
    $css = (string) file_get_contents(resource_path("css/filament/{$theme}/theme.css"));

    expect($css)->toContain("@import '../../kneadit/admin-aliases.css';");
})->with(['admin', 'central']);

test('both panel themes import the tokens and the design system overrides', function (string $theme) {
    $css = (string) file_get_contents(resource_path("css/filament/{$theme}/theme.css"));

    expect($css)->toContain("@import '../../kneadit/tokens.css';")
        ->and($css)->toContain("@import '../../kneadit/filament.css';");
})->with(['admin', 'central']);

test('primary buttons use the on-honey text colour and headings use the display font', function () {
    $css = (string) file_get_contents(resource_path('css/kneadit/filament.css'));

    expect($css)->toContain('.fi-btn.fi-color-primary')
        ->and($css)->toContain('var(--kn-on-honey)')
        ->and($css)->toContain('.fi-header-heading')
        ->and($css)->toContain('var(--kn-font-display)');
});

test('the aliases nothing reads any more are gone', function (string $variable) {
    $css = (string) file_get_contents(resource_path('css/kneadit/admin-aliases.css'));

    expect($css)->not->toContain($variable);
})->with([
    '--border-medium', '--hover-bg', '--active-bg', '--scrollbar-thumb', '--focus-ring', '--thead-bg',
    '--color-warm-black', '--color-espresso', '--color-cinnamon', '--color-honey', '--color-golden', '--color-butter', '--color-parchment',
]);
