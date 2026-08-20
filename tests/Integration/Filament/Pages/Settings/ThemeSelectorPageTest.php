<?php

use App\Filament\Pages\Settings\ThemeSelector;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new ThemeSelector;
});

test('get title returns storefront theme', function () {
    expect(testFixture('page', ThemeSelector::class)->getTitle())->toBe('Storefront Theme');
});

test('select theme saves valid theme', function () {
    testFixture('page', ThemeSelector::class)->selectTheme('modern');

    expect(settings('storefront_theme'))->toBe('modern');
});

test('select theme ignores invalid theme', function () {
    settings(['storefront_theme' => 'classic']);

    testFixture('page', ThemeSelector::class)->selectTheme('nonexistent_theme_xyz');

    expect(settings('storefront_theme'))->toBe('classic');
});
