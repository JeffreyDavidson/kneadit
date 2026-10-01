<?php

declare(strict_types=1);

namespace App\Enums\Inventory;

use Filament\Support\Contracts\HasLabel;

/**
 * The units an ingredient is stocked in and a recipe line is measured in.
 *
 * Each unit belongs to a dimension and carries a factor to that dimension's
 * base unit (grams, millilitres, each), which is what makes conversion possible.
 */
enum MeasurementUnit: string implements HasLabel
{
    case Ounces = 'oz';
    case Pounds = 'lbs';
    case Grams = 'g';
    case Kilograms = 'kg';
    case Cups = 'cups';
    case Tablespoons = 'tbsp';
    case Teaspoons = 'tsp';
    case Milliliters = 'ml';
    case Liters = 'l';
    case Each = 'each';
    case Dozen = 'dozen';

    /**
     * Option list for a select, optionally limited to one dimension.
     *
     * @return array<string, string>
     */
    public static function options(?UnitDimension $dimension = null): array
    {
        $options = [];

        foreach (self::cases() as $unit) {
            if (! $dimension instanceof UnitDimension || $unit->dimension() === $dimension) {
                $options[$unit->value] = $unit->getLabel();
            }
        }

        return $options;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Ounces => 'Ounces (oz)',
            self::Pounds => 'Pounds (lbs)',
            self::Grams => 'Grams (g)',
            self::Kilograms => 'Kilograms (kg)',
            self::Cups => 'Cups',
            self::Tablespoons => 'Tablespoons',
            self::Teaspoons => 'Teaspoons',
            self::Milliliters => 'Milliliters (ml)',
            self::Liters => 'Liters (l)',
            self::Each => 'Each',
            self::Dozen => 'Dozen',
        };
    }

    public function dimension(): UnitDimension
    {
        return match ($this) {
            self::Ounces, self::Pounds, self::Grams, self::Kilograms => UnitDimension::Mass,
            self::Cups, self::Tablespoons, self::Teaspoons, self::Milliliters, self::Liters => UnitDimension::Volume,
            self::Each, self::Dozen => UnitDimension::Count,
        };
    }

    /**
     * Converts a quantity of this unit into the target unit, or null when the
     * units measure different dimensions (cups of flour to pounds needs a density).
     */
    public function convert(float $quantity, self $target): ?float
    {
        if ($this->dimension() !== $target->dimension()) {
            return null;
        }

        return $quantity * $this->baseFactor() / $target->baseFactor();
    }

    /**
     * How many of the dimension's base unit (grams, millilitres, each) one of this unit is.
     */
    private function baseFactor(): float
    {
        return match ($this) {
            self::Ounces => 28.349523125,
            self::Pounds => 453.59237,
            self::Grams => 1.0,
            self::Kilograms => 1000.0,
            self::Teaspoons => 4.92892159375,
            self::Tablespoons => 14.78676478125,
            self::Cups => 236.5882365,
            self::Milliliters => 1.0,
            self::Liters => 1000.0,
            self::Each => 1.0,
            self::Dozen => 12.0,
        };
    }
}
