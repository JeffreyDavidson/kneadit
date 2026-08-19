<?php

use App\Filament\Pages\Settings\HomepageBuilder;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new HomepageBuilder;
});

test('mount loads default sections', function () {
    testFixture('page', HomepageBuilder::class)->mount();

    expect(testFixture('page', HomepageBuilder::class)->sections)->toBeArray()->not->toBeEmpty()
        ->and(testFixture('page', HomepageBuilder::class)->sections)->toHaveKeys(['hero', 'about', 'featured_products', 'categories', 'reviews', 'gallery', 'blog', 'cta', 'social']);
});

test('each section has visible and order keys', function () {
    testFixture('page', HomepageBuilder::class)->mount();

    foreach (testFixture('page', HomepageBuilder::class)->sections as $key => $section) {
        expect($section)->toHaveKeys(['visible', 'order'], "Section '{$key}' missing expected keys");
    }
});

test('toggle visibility flips visible flag', function () {
    testFixture('page', HomepageBuilder::class)->mount();

    expect(testFixture('page', HomepageBuilder::class)->sections['hero']['visible'])->toBeTrue();

    testFixture('page', HomepageBuilder::class)->toggleVisibility('hero');

    expect(testFixture('page', HomepageBuilder::class)->sections['hero']['visible'])->toBeFalse();

    testFixture('page', HomepageBuilder::class)->toggleVisibility('hero');

    expect(testFixture('page', HomepageBuilder::class)->sections['hero']['visible'])->toBeTrue();
});

test('move up swaps order with previous section', function () {
    testFixture('page', HomepageBuilder::class)->mount();

    $aboutOrder = testFixture('page', HomepageBuilder::class)->sections['about']['order'];
    $heroOrder = testFixture('page', HomepageBuilder::class)->sections['hero']['order'];

    testFixture('page', HomepageBuilder::class)->moveUp('about');

    expect(testFixture('page', HomepageBuilder::class)->sections['about']['order'])->toBe($heroOrder)
        ->and(testFixture('page', HomepageBuilder::class)->sections['hero']['order'])->toBe($aboutOrder);
});

test('move up on first section does nothing', function () {
    testFixture('page', HomepageBuilder::class)->mount();

    $heroOrder = testFixture('page', HomepageBuilder::class)->sections['hero']['order'];

    testFixture('page', HomepageBuilder::class)->moveUp('hero');

    expect(testFixture('page', HomepageBuilder::class)->sections['hero']['order'])->toBe($heroOrder);
});

test('move down swaps order with next section', function () {
    testFixture('page', HomepageBuilder::class)->mount();

    $heroOrder = testFixture('page', HomepageBuilder::class)->sections['hero']['order'];
    $aboutOrder = testFixture('page', HomepageBuilder::class)->sections['about']['order'];

    testFixture('page', HomepageBuilder::class)->moveDown('hero');

    expect(testFixture('page', HomepageBuilder::class)->sections['hero']['order'])->toBe($aboutOrder)
        ->and(testFixture('page', HomepageBuilder::class)->sections['about']['order'])->toBe($heroOrder);
});

test('move down on last section does nothing', function () {
    testFixture('page', HomepageBuilder::class)->mount();

    $socialOrder = testFixture('page', HomepageBuilder::class)->sections['social']['order'];

    testFixture('page', HomepageBuilder::class)->moveDown('social');

    expect(testFixture('page', HomepageBuilder::class)->sections['social']['order'])->toBe($socialOrder);
});

test('update section field sets value', function () {
    testFixture('page', HomepageBuilder::class)->mount();

    testFixture('page', HomepageBuilder::class)->updateSectionField('featured_products', 'title', 'Best Sellers');

    expect(testFixture('page', HomepageBuilder::class)->sections['featured_products']['title'])->toBe('Best Sellers');
});

test('save persists sections to settings', function () {
    testFixture('page', HomepageBuilder::class)->mount();
    testFixture('page', HomepageBuilder::class)->toggleVisibility('hero');
    testFixture('page', HomepageBuilder::class)->save();

    $saved = json_decode(settings('homepage_sections'), true);

    expect($saved['hero']['visible'])->toBeFalse();
});

test('reset to defaults restores original values', function () {
    testFixture('page', HomepageBuilder::class)->mount();
    testFixture('page', HomepageBuilder::class)->toggleVisibility('hero');
    testFixture('page', HomepageBuilder::class)->toggleVisibility('about');
    testFixture('page', HomepageBuilder::class)->save();

    testFixture('page', HomepageBuilder::class)->resetToDefaults();

    expect(testFixture('page', HomepageBuilder::class)->sections['hero']['visible'])->toBeTrue()
        ->and(testFixture('page', HomepageBuilder::class)->sections['about']['visible'])->toBeTrue();
});

test('get sorted sections returns sections sorted by order', function () {
    testFixture('page', HomepageBuilder::class)->mount();

    $sorted = testFixture('page', HomepageBuilder::class)->getSortedSections();
    $orders = array_column($sorted, 'order');

    expect($orders)->toBe(array_values(array_unique($orders)))
        ->toEqual(collect($orders)->sort()->values()->all());
});

test('get section meta returns label and description', function () {
    testFixture('page', HomepageBuilder::class)->mount();

    $meta = testFixture('page', HomepageBuilder::class)->getSectionMeta('hero');

    expect($meta)->toHaveKeys(['label', 'description'])
        ->and($meta['label'])->toBe('Hero Banner');
});

test('get section meta returns default for unknown key', function () {
    testFixture('page', HomepageBuilder::class)->mount();

    $meta = testFixture('page', HomepageBuilder::class)->getSectionMeta('unknown_section');

    expect($meta['label'])->toBe('Unknown_section')
        ->and($meta['description'])->toBeEmpty();
});

test('mount loads saved sections merged with defaults', function () {
    settings(['homepage_sections' => json_encode(['hero' => ['visible' => false, 'order' => 1]])]);

    testFixture('page', HomepageBuilder::class)->mount();

    expect(testFixture('page', HomepageBuilder::class)->sections['hero']['visible'])->toBeFalse()
        ->and(testFixture('page', HomepageBuilder::class)->sections['about']['visible'])->toBeTrue(); // default
});

test('mount loads hero tagline and CTA text from settings', function () {
    settings([
        'hero_tagline' => 'Baked with love',
        'hero_primary_cta_text' => 'Place Your Order',
        'hero_secondary_cta_text' => 'See Our Menu',
    ]);

    testFixture('page', HomepageBuilder::class)->mount();

    expect(testFixture('page', HomepageBuilder::class)->hero_tagline)->toBe('Baked with love')
        ->and(testFixture('page', HomepageBuilder::class)->hero_primary_cta_text)->toBe('Place Your Order')
        ->and(testFixture('page', HomepageBuilder::class)->hero_secondary_cta_text)->toBe('See Our Menu');
});

test('mount falls back to default CTA text when unset', function () {
    testFixture('page', HomepageBuilder::class)->mount();

    expect(testFixture('page', HomepageBuilder::class)->hero_primary_cta_text)->toBe('Order Now')
        ->and(testFixture('page', HomepageBuilder::class)->hero_secondary_cta_text)->toBe('Browse Menu')
        ->and(testFixture('page', HomepageBuilder::class)->hero_tagline)->toBeNull();
});

test('save persists hero tagline and CTA text', function () {
    testFixture('page', HomepageBuilder::class)->mount();
    testFixture('page', HomepageBuilder::class)->hero_tagline = 'Fresh every morning';
    testFixture('page', HomepageBuilder::class)->hero_primary_cta_text = 'Start Your Order';
    testFixture('page', HomepageBuilder::class)->hero_secondary_cta_text = 'View Our Menu';

    testFixture('page', HomepageBuilder::class)->save();

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
    testFixture('page', HomepageBuilder::class)->mount();

    testFixture('page', HomepageBuilder::class)->resetToDefaults();

    expect(testFixture('page', HomepageBuilder::class)->hero_tagline)->toBe('Where every bite tells a story')
        ->and(testFixture('page', HomepageBuilder::class)->hero_primary_cta_text)->toBe('Order Now')
        ->and(testFixture('page', HomepageBuilder::class)->hero_secondary_cta_text)->toBe('Browse Menu');
});
