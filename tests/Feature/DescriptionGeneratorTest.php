<?php

use App\Services\DescriptionGeneratorService;

beforeEach(function () {
    $this->service = new DescriptionGeneratorService;
});

test('service exists', function () {
    expect($this->service)->toBeInstanceOf(DescriptionGeneratorService::class);
});

test('generate returns array of 3 descriptions', function () {
    $result = $this->service->generate('Sourdough Bread', 'professional', 'medium', 'bread');

    expect($result)->toBeArray();
    expect($result)->toHaveCount(3);
});

test('each description contains product name', function () {
    $result = $this->service->generate('Sourdough Bread', 'professional', 'medium', 'bread');

    foreach ($result as $description) {
        expect($description)->toContain('Sourdough Bread');
    }
});

test('different tones produce different text', function () {
    $professional = $this->service->generate('Croissant', 'professional', 'medium', 'pastry');
    $playful = $this->service->generate('Croissant', 'playful', 'medium', 'pastry');

    expect($professional)->not->toBe($playful);
});

test('short length produces shorter text', function () {
    $short = $this->service->generate('Sourdough', 'professional', 'short', 'bread');
    $long = $this->service->generate('Sourdough', 'professional', 'long', 'bread');

    $shortWords = str_word_count(implode(' ', $short));
    $longWords = str_word_count(implode(' ', $long));

    expect($shortWords)->toBeLessThan($longWords);
});

test('custom count returns requested number', function () {
    $result = $this->service->generate('Muffin', 'casual', 'medium', 'pastry', null, 2);

    expect($result)->toHaveCount(2);
});

test('unknown tone falls back to professional', function () {
    $result = $this->service->generate('Bread', 'nonexistent_tone', 'medium');

    expect($result)->toBeArray();
    expect($result)->toHaveCount(3);
});

test('null category uses default', function () {
    $result = $this->service->generate('Mystery Item', 'casual', 'medium');

    foreach ($result as $description) {
        expect($description)->toContain('Mystery Item');
    }
});
