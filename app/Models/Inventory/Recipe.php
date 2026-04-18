<?php

namespace App\Models\Inventory;

use Database\Factories\Inventory\RecipeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property array<int, array{name?: string, cost?: float, quantity?: float, unit?: string}> $ingredients
 * @property-read Collection<int, Ingredient> $inventoryIngredients
 * @property-read int|null $inventory_ingredients_count
 * @property-read Product|null $product
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe query()
 *
 * @mixin \Eloquent
 */
#[Fillable('product_id', 'name', 'ingredients', 'instructions', 'prep_time_minutes', 'cost')]
class Recipe extends Model
{
    /** @use HasFactory<RecipeFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'ingredients' => 'json',
            'prep_time_minutes' => 'integer',
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

    /**
     * @return BelongsToMany<Ingredient, $this, Pivot>
     */
    public function inventoryIngredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'recipe_ingredients')
            ->withPivot('quantity', 'unit');
    }

    protected static function newFactory(): RecipeFactory
    {
        return RecipeFactory::new();
    }
}
