<?php

namespace App\Models\Inventory;

use App\Enums\Inventory\StockAdjustmentType;
use Database\Factories\Inventory\StockAdjustmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read Ingredient|null $ingredient
 *
 * @method static \Database\Factories\StockAdjustmentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockAdjustment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockAdjustment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockAdjustment query()
 *
 * @mixin \Eloquent
 */
class StockAdjustment extends Model
{
    /** @use HasFactory<StockAdjustmentFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'ingredient_id',
        'quantity',
        'type',
        'notes',
    ];

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

    protected static function newFactory(): StockAdjustmentFactory
    {
        return StockAdjustmentFactory::new();
    }
}
