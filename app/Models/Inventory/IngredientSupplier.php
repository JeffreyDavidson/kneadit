<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use App\Casts\MoneyCentsCast;
use App\ValueObjects\Money;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * One supplier's offer for one ingredient: the `ingredient_supplier` pivot row.
 *
 * `unit_price` and `minimum_order` are integer cents (migration
 * 2026_04_22_240000), so they carry the same money cast as every other money
 * column. Attaching or updating through a relation that uses this class
 * therefore takes dollars and stores cents.
 *
 * @property Money|null $unit_price
 * @property Money|null $minimum_order
 * @property int|null $lead_time_days
 * @property string|null $sku
 */
class IngredientSupplier extends Pivot
{
    #[\Override]
    protected function casts(): array
    {
        return [
            'unit_price' => MoneyCentsCast::class,
            'minimum_order' => MoneyCentsCast::class,
        ];
    }
}
