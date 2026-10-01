<?php

namespace App\Models\Inventory;

use App\Casts\MoneyCentsCast;
use App\ValueObjects\Money;
use Database\Factories\Inventory\RecipeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property array<int, array{name?: string, cost?: float, quantity?: float, unit?: string}> $ingredients
 * @property-read Collection<int, RecipeIngredient> $ingredientLines
 * @property-read int|null $ingredient_lines_count
 * @property-read Collection<int, Ingredient> $inventoryIngredients
 * @property-read int|null $inventory_ingredients_count
 * @property-read Product|null $product
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe query()
 *
 * @property Money|null $cost
 *
 * @mixin \Eloquent
 */
#[Fillable('product_id', 'name', 'ingredients', 'instructions', 'prep_time_minutes', 'cost')]
#[UseFactory(RecipeFactory::class)]
class Recipe extends Model
{
    /** @use HasFactory<RecipeFactory> */
    use HasFactory;

    #[\Override]
    protected function casts(): array
    {
        return [
            'ingredients' => 'json',
            'prep_time_minutes' => 'integer',
            'cost' => MoneyCentsCast::class,
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

    /**
     * The linked-ingredient lines as models, for editing their quantity and unit. The same
     * `recipe_ingredients` rows are read as ingredients (with a pivot) via inventoryIngredients().
     *
     * @return HasMany<RecipeIngredient, $this>
     */
    public function ingredientLines(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }
}
