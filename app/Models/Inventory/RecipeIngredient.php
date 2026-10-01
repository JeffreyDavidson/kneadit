<?php

namespace App\Models\Inventory;

use Database\Factories\Inventory\RecipeIngredientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One linked-ingredient line of a recipe: the `recipe_ingredients` pivot row as a model,
 * so the recipe form can edit its quantity and unit as a HasMany repeater.
 *
 * The unit stays a plain string (not a MeasurementUnit cast) so a legacy value that isn't
 * a known unit still loads, as it does through `Recipe::inventoryIngredients()`.
 *
 * @property-read Ingredient|null $ingredient
 * @property-read Recipe|null $recipe
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeIngredient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeIngredient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeIngredient query()
 *
 * @mixin \Eloquent
 */
#[Table('recipe_ingredients')]
#[WithoutTimestamps]
#[Fillable('ingredient_id', 'quantity', 'unit')]
#[UseFactory(RecipeIngredientFactory::class)]
class RecipeIngredient extends Model
{
    /** @use HasFactory<RecipeIngredientFactory> */
    use HasFactory;

    #[\Override]
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
        ];
    }

    /**
     * @return BelongsTo<Recipe, $this>
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * @return BelongsTo<Ingredient, $this>
     */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
