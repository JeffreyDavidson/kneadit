<?php

use App\Services\Content\DescriptionGeneratorService;

test('generates descriptions with correct count', function () {
    $service = new DescriptionGeneratorService;

    $descriptions = $service->generate('Chocolate Cake', 'professional', 'medium', 'Cakes');

    expect($descriptions)->toHaveCount(3)
        ->each->toBeString()->not->toBeEmpty();
});

test('descriptions contain product name', function () {
    $service = new DescriptionGeneratorService;

    $descriptions = $service->generate('Sourdough Loaf', 'casual', 'medium', 'Bread');

    expect($descriptions)->each(fn ($desc) => $desc->toContain('Sourdough Loaf'));
});

test('generates different tones', function (string $tone) {
    $service = new DescriptionGeneratorService;

    $descriptions = $service->generate('Muffin', $tone, 'medium');

    expect($descriptions)->toHaveCount(3)
        ->each->toBeString()->not->toBeEmpty();
})->with(['professional', 'casual', 'playful', 'storytelling']);

test('respects custom count', function () {
    $service = new DescriptionGeneratorService;

    $descriptions = $service->generate('Cookie', 'casual', 'short', count: 5);

    expect($descriptions)->toHaveCount(5);
});
