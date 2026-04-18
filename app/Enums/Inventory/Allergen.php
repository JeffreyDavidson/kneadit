<?php

namespace App\Enums\Inventory;

use Filament\Support\Contracts\HasLabel;

/**
 * The FDA "Big 9" major food allergens — these are what cottage food labels
 * must disclose in the US. Sesame was added in 2023 by the FASTER Act.
 */
enum Allergen: string implements HasLabel
{
    case Milk = 'milk';
    case Eggs = 'eggs';
    case Fish = 'fish';
    case Shellfish = 'shellfish';
    case TreeNuts = 'tree_nuts';
    case Peanuts = 'peanuts';
    case Wheat = 'wheat';
    case Soybeans = 'soybeans';
    case Sesame = 'sesame';

    public function getLabel(): string
    {
        return match ($this) {
            self::Milk => 'Milk',
            self::Eggs => 'Eggs',
            self::Fish => 'Fish',
            self::Shellfish => 'Shellfish',
            self::TreeNuts => 'Tree nuts',
            self::Peanuts => 'Peanuts',
            self::Wheat => 'Wheat',
            self::Soybeans => 'Soybeans',
            self::Sesame => 'Sesame',
        };
    }
}
