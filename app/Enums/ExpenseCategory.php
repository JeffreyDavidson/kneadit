<?php

namespace App\Enums;

enum ExpenseCategory: string
{
    case Supplies = 'supplies';
    case Ingredients = 'ingredients';
    case Packaging = 'packaging';
    case BoothFees = 'booth_fees';
    case Delivery = 'delivery';
    case Marketing = 'marketing';
    case Insurance = 'insurance';
    case Education = 'education';
    case Equipment = 'equipment';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Supplies => 'Supplies',
            self::Ingredients => 'Ingredients',
            self::Packaging => 'Packaging',
            self::BoothFees => 'Booth Fees',
            self::Delivery => 'Delivery',
            self::Marketing => 'Marketing',
            self::Insurance => 'Insurance',
            self::Education => 'Education',
            self::Equipment => 'Equipment',
            self::Other => 'Other',
        };
    }
}
