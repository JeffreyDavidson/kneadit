<?php

namespace App\Models\Inventory;

use App\Builders\Inventory\CategoryQueryBuilder;
use App\Observers\LogsActivityObserver;
use Database\Factories\Inventory\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read Collection<int, Product> $products
 * @property-read int|null $products_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 *
 * @mixin \Eloquent
 */
#[Fillable('name', 'slug', 'description', 'is_active', 'sort_order')]
#[ObservedBy(LogsActivityObserver::class)]
#[UseEloquentBuilder(CategoryQueryBuilder::class)]
#[UseFactory(CategoryFactory::class)]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
