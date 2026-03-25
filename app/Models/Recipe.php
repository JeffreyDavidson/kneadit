<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'ingredients',
        'instructions',
        'prep_time_minutes',
        'cost',
    ];

    protected $casts = [
        'ingredients' => 'json',
        'cost' => 'decimal:2',
    ];

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
}
