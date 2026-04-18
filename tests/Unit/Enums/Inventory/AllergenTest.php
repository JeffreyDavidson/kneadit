<?php

use App\Enums\Inventory\Allergen;

test('contains the FDA Big 9 allergens', function () {
    expect(Allergen::cases())->toHaveCount(9)
        ->and(collect(Allergen::cases())->pluck('value')->all())->toEqual([
            'milk', 'eggs', 'fish', 'shellfish', 'tree_nuts', 'peanuts', 'wheat', 'soybeans', 'sesame',
        ]);
});

test('each case produces a readable label', function (Allergen $allergen, string $expected) {
    expect($allergen->getLabel())->toBe($expected);
})->with([
    [Allergen::Milk, 'Milk'],
    [Allergen::TreeNuts, 'Tree nuts'],
    [Allergen::Soybeans, 'Soybeans'],
    [Allergen::Sesame, 'Sesame'],
]);
