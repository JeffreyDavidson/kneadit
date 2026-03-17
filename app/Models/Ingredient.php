<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Ingredient extends Model
{
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

    protected $casts = [
        'current_stock' => 'decimal:2',
        'low_stock_threshold' => 'decimal:2',
        'cost_per_unit' => 'decimal:2',
    ];

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

    public function isLowStock(): bool
    {
        return $this->current_stock > 0 && $this->current_stock <= $this->low_stock_threshold;
    }

    public function isOutOfStock(): bool
    {
        return $this->current_stock <= 0;
    }

    public function getStockStatus(): string
    {
        if ($this->isOutOfStock()) {
            return 'out';
        }
        if ($this->isLowStock()) {
            return 'low';
        }

        return 'good';
    }

    public function adjustStock(float $quantity, string $type, ?string $notes = null): void
    {
        $this->increment('current_stock', $quantity);

        $this->stockAdjustments()->create([
            'quantity' => $quantity,
            'type' => $type,
            'notes' => $notes,
        ]);
    }
}
