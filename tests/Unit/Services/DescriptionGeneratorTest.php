<?php

use App\Services\Content\DescriptionGeneratorService;

beforeEach(function () {
    test()->service = new DescriptionGeneratorService;
});

test('service exists', function () {
    expect(test()->service)->toBeInstanceOf(DescriptionGeneratorService::class);
});

test('generate returns array of 3 descriptions', function () {
    $result = test()->service->generate('Sourdough Bread', 'professional', 'medium', 'bread');

    expect($result)->toBeArray()->toHaveCount(3);
});

test('each description contains product name', function () {
    $result = test()->service->generate('Sourdough Bread', 'professional', 'medium', 'bread');

    expect($result)->each->toContain('Sourdough Bread');
});

test('different tones produce different text', function () {
    $professional = test()->service->generate('Croissant', 'professional', 'medium', 'pastry');
    $playful = test()->service->generate('Croissant', 'playful', 'medium', 'pastry');

    expect($professional)->not->toBe($playful);
});

test('short length produces shorter text', function () {
    $short = test()->service->generate('Sourdough', 'professional', 'short', 'bread');
    $long = test()->service->generate('Sourdough', 'professional', 'long', 'bread');

    $shortWords = str_word_count(implode(' ', $short));
    $longWords = str_word_count(implode(' ', $long));

    expect($shortWords)->toBeLessThan($longWords);
});

test('custom count returns requested number', function () {
    $result = test()->service->generate('Muffin', 'casual', 'medium', 'pastry', null, 2);

    expect($result)->toHaveCount(2);
});

test('unknown tone falls back to professional', function () {
    $result = test()->service->generate('Bread', 'nonexistent_tone', 'medium');

    expect($result)->toBeArray()->toHaveCount(3);
});

test('null category uses default', function () {
    $result = test()->service->generate('Mystery Item', 'casual', 'medium');

    expect($result)->each->toContain('Mystery Item');
});
