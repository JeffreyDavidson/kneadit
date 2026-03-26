<?php

namespace App\Models;

use Database\Factories\RecipeFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property array<int, array{name?: string, cost?: float, quantity?: float, unit?: string}> $ingredients
 * @property-read float|null $profit_margin
 * @property-read Collection<int, Ingredient> $inventoryIngredients
 * @property-read int|null $inventory_ingredients_count
 * @property-read Product|null $product
 *
 * @method static \Database\Factories\RecipeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe query()
 *
 * @mixin \Eloquent
 */
class Recipe extends Model
{
    /** @use HasFactory<RecipeFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'ingredients',
        'instructions',
        'prep_time_minutes',
        'cost',
    ];

    protected function casts(): array
    {
        return [
            'ingredients' => 'json',
            'cost' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return Attribute<mixed, never> */
    protected function profitMargin(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->product || ! $this->cost || $this->product->price <= 0) {
                    return null;
                }

                return round((($this->product->price - $this->cost) / $this->product->price) * 100, 1);
            },
        );
    }

    /**
     * @return BelongsToMany<Ingredient, $this, Pivot>
     */
    public function inventoryIngredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'recipe_ingredients')
            ->withPivot('quantity', 'unit');
    }
}
