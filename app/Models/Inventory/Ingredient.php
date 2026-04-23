<?php

namespace App\Models\Inventory;

use App\Casts\MoneyCentsCast;
use App\Enums\Inventory\Allergen;
use App\Observers\LogsActivityObserver;
use Database\Factories\Inventory\IngredientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property \Illuminate\Support\Collection<int, Allergen>|null $allergens
 * @property-read Collection<int, Recipe> $recipes
 * @property-read int|null $recipes_count
 * @property-read Collection<int, StockAdjustment> $stockAdjustments
 * @property-read int|null $stock_adjustments_count
 * @property-read Collection<int, Supplier> $suppliers
 * @property-read int|null $suppliers_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredient query()
 *
 * @property-read Pivot|null $pivot
 * @property \App\ValueObjects\Money|null $cost_per_unit
 *
 * @mixin \Eloquent
 */
#[Fillable('name', 'unit', 'current_stock', 'low_stock_threshold', 'cost_per_unit', 'supplier', 'notes', 'allergens')]
#[ObservedBy(LogsActivityObserver::class)]
#[UseFactory(IngredientFactory::class)]
class Ingredient extends Model
{
    /** @use HasFactory<IngredientFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'current_stock' => 'decimal:2',
            'low_stock_threshold' => 'decimal:2',
            'cost_per_unit' => MoneyCentsCast::class,
            'allergens' => AsEnumCollection::of(Allergen::class),
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
}
