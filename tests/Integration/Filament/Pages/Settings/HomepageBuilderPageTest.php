<?php

use App\Filament\Pages\Settings\HomepageBuilder;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new HomepageBuilder;
});

test('mount loads default sections', function () {
    test()->page->mount();

    expect(test()->page->sections)->toBeArray()->not->toBeEmpty()
        ->and(test()->page->sections)->toHaveKeys(['hero', 'about', 'featured_products', 'categories', 'reviews', 'gallery', 'blog', 'cta', 'social']);
});

test('each section has visible and order keys', function () {
    test()->page->mount();

    foreach (test()->page->sections as $key => $section) {
        expect($section)->toHaveKeys(['visible', 'order'], "Section '{$key}' missing expected keys");
    }
});

test('toggle visibility flips visible flag', function () {
    test()->page->mount();

    expect(test()->page->sections['hero']['visible'])->toBeTrue();

    test()->page->toggleVisibility('hero');

    expect(test()->page->sections['hero']['visible'])->toBeFalse();

    test()->page->toggleVisibility('hero');

    expect(test()->page->sections['hero']['visible'])->toBeTrue();
});

test('move up swaps order with previous section', function () {
    test()->page->mount();

    $aboutOrder = test()->page->sections['about']['order'];
    $heroOrder = test()->page->sections['hero']['order'];

    test()->page->moveUp('about');

    expect(test()->page->sections['about']['order'])->toBe($heroOrder)
        ->and(test()->page->sections['hero']['order'])->toBe($aboutOrder);
});

test('move up on first section does nothing', function () {
    test()->page->mount();

    $heroOrder = test()->page->sections['hero']['order'];

    test()->page->moveUp('hero');

    expect(test()->page->sections['hero']['order'])->toBe($heroOrder);
});

test('move down swaps order with next section', function () {
    test()->page->mount();

    $heroOrder = test()->page->sections['hero']['order'];
    $aboutOrder = test()->page->sections['about']['order'];

    test()->page->moveDown('hero');

    expect(test()->page->sections['hero']['order'])->toBe($aboutOrder)
        ->and(test()->page->sections['about']['order'])->toBe($heroOrder);
});

test('move down on last section does nothing', function () {
    test()->page->mount();

    $socialOrder = test()->page->sections['social']['order'];

    test()->page->moveDown('social');

    expect(test()->page->sections['social']['order'])->toBe($socialOrder);
});

test('update section field sets value', function () {
    test()->page->mount();

    test()->page->updateSectionField('featured_products', 'title', 'Best Sellers');

    expect(test()->page->sections['featured_products']['title'])->toBe('Best Sellers');
});

test('save persists sections to settings', function () {
    test()->page->mount();
    test()->page->toggleVisibility('hero');
    test()->page->save();

    $savedSections = settings('homepage_sections');
    throw_unless(is_string($savedSections), UnexpectedValueException::class, 'Expected saved homepage sections JSON.');

    $saved = json_decode($savedSections, true);
    throw_unless(is_array($saved), UnexpectedValueException::class, 'Expected decoded homepage sections.');
    throw_unless(isset($saved['hero']) && is_array($saved['hero']), UnexpectedValueException::class, 'Expected saved hero section.');

    expect($saved['hero']['visible'])->toBeFalse();
});

test('reset to defaults restores original values', function () {
    test()->page->mount();
    test()->page->toggleVisibility('hero');
    test()->page->toggleVisibility('about');
    test()->page->save();

    test()->page->resetToDefaults();

    expect(test()->page->sections['hero']['visible'])->toBeTrue()
        ->and(test()->page->sections['about']['visible'])->toBeTrue();
});

test('get sorted sections returns sections sorted by order', function () {
    test()->page->mount();

    $sorted = test()->page->getSortedSections();
    $orders = array_column($sorted, 'order');

    expect($orders)->toBe(array_values(array_unique($orders)))
        ->toEqual(collect($orders)->sort()->values()->all());
});

test('get section meta returns label and description', function () {
    test()->page->mount();

    $meta = test()->page->getSectionMeta('hero');

    expect($meta)->toHaveKeys(['label', 'description'])
        ->and($meta['label'])->toBe('Hero Banner');
});

test('get section meta returns default for unknown key', function () {
    test()->page->mount();

    $meta = test()->page->getSectionMeta('unknown_section');

    expect($meta['label'])->toBe('Unknown_section')
        ->and($meta['description'])->toBeEmpty();
});

test('mount loads saved sections merged with defaults', function () {
    settings(['homepage_sections' => json_encode(['hero' => ['visible' => false, 'order' => 1]])]);

    test()->page->mount();

    expect(test()->page->sections['hero']['visible'])->toBeFalse()
        ->and(test()->page->sections['about']['visible'])->toBeTrue(); // default
});

test('mount loads hero tagline and CTA text from settings', function () {
    settings([
        'hero_tagline' => 'Baked with love',
        'hero_primary_cta_text' => 'Place Your Order',
        'hero_secondary_cta_text' => 'See Our Menu',
    ]);

    test()->page->mount();

    expect(test()->page->hero_tagline)->toBe('Baked with love')
        ->and(test()->page->hero_primary_cta_text)->toBe('Place Your Order')
        ->and(test()->page->hero_secondary_cta_text)->toBe('See Our Menu');
});

test('mount falls back to default CTA text when unset', function () {
    test()->page->mount();

    expect(test()->page->hero_primary_cta_text)->toBe('Order Now')
        ->and(test()->page->hero_secondary_cta_text)->toBe('Browse Menu')
        ->and(test()->page->hero_tagline)->toBeNull();
});

test('save persists hero tagline and CTA text', function () {
    test()->page->mount();
    test()->page->hero_tagline = 'Fresh every morning';
    test()->page->hero_primary_cta_text = 'Start Your Order';
    test()->page->hero_secondary_cta_text = 'View Our Menu';

    test()->page->save();

    expect(settings('hero_tagline'))->toBe('Fresh every morning')
        ->and(settings('hero_primary_cta_text'))->toBe('Start Your Order')
        ->and(settings('hero_secondary_cta_text'))->toBe('View Our Menu');
});

test('reset to defaults restores hero CTA text', function () {
    settings([
        'hero_tagline' => 'Custom',
        'hero_primary_cta_text' => 'Custom Primary',
        'hero_secondary_cta_text' => 'Custom Secondary',
    ]);
    test()->page->mount();

    test()->page->resetToDefaults();

    expect(test()->page->hero_tagline)->toBe('Where every bite tells a story')
        ->and(test()->page->hero_primary_cta_text)->toBe('Order Now')
        ->and(test()->page->hero_secondary_cta_text)->toBe('Browse Menu');
});
