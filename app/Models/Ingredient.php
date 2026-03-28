<?php

namespace App\Models;

use App\Enums\StockStatus;
use App\Traits\LogsActivity;
use Database\Factories\IngredientFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property-read Collection<int, Recipe> $recipes
 * @property-read int|null $recipes_count
 * @property-read Collection<int, StockAdjustment> $stockAdjustments
 * @property-read int|null $stock_adjustments_count
 * @property-read Collection<int, Supplier> $suppliers
 * @property-read int|null $suppliers_count
 *
 * @method static \Database\Factories\IngredientFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredient query()
 *
 * @property-read Pivot|null $pivot
 *
 * @mixin \Eloquent
 */
class Ingredient extends Model
{
    /** @use HasFactory<IngredientFactory> */
    use HasFactory;

    use LogsActivity;

    protected $fillable = [
        'name',
        'unit',
        'current_stock',
        'low_stock_threshold',
        'cost_per_unit',
        'supplier',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'current_stock' => 'decimal:2',
            'low_stock_threshold' => 'decimal:2',
            'cost_per_unit' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsToMany<Recipe, $this, Pivot>
     */
    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'recipe_ingredients')
            ->withPivot('quantity', 'unit');
    }

    /**
     * @return BelongsToMany<Supplier, $this, Pivot>
     */
    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'ingredient_supplier')
            ->withPivot('unit_price', 'minimum_order', 'lead_time_days', 'sku')
            ->withTimestamps();
    }

    /**
     * @return HasMany<StockAdjustment, $this>
     */
    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    /** @return Attribute<bool, never> */
    protected function isLowStock(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->current_stock > 0 && $this->current_stock <= $this->low_stock_threshold,
        );
    }

    /** @return Attribute<bool, never> */
    protected function isOutOfStock(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->current_stock <= 0,
        );
    }

    /** @return Attribute<StockStatus, never> */
    protected function stockStatus(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->is_out_of_stock) {
                    return StockStatus::Out;
                }
                if ($this->is_low_stock) {
                    return StockStatus::Low;
                }

                return StockStatus::Good;
            },
        );
    }
}
