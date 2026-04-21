<?php

namespace App\Models\Inventory;

use App\Enums\Inventory\StockAdjustmentType;
use Database\Factories\Inventory\StockAdjustmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read Ingredient|null $ingredient
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockAdjustment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockAdjustment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockAdjustment query()
 *
 * @mixin \Eloquent
 */
#[WithoutTimestamps]
#[Fillable('ingredient_id', 'quantity', 'type', 'notes')]
#[UseFactory(StockAdjustmentFactory::class)]
class StockAdjustment extends Model
{
    /** @use HasFactory<StockAdjustmentFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'created_at' => 'datetime',
            'type' => StockAdjustmentType::class,
        ];
    }

    /**
     * @return BelongsTo<Ingredient, $this>
     */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
