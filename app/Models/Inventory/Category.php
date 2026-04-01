<?php

namespace App\Models\Inventory;

use App\Models\Concerns\LogsActivity;
use Database\Factories\Inventory\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read Collection<int, Product> $products
 * @property-read int|null $products_count
 *
 * @method static \Database\Factories\CategoryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 *
 * @mixin \Eloquent
 */
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    use LogsActivity;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
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

    /** @param Builder<Category> $query */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /** @param Builder<Category> $query */
    #[Scope]
    protected function withActiveProducts(Builder $query): void
    {
        $query->with(['products' => fn (HasMany $q) => $q->where('is_active', true)->orderBy('name')]);
    }

    /** @param Builder<Category> $query */
    #[Scope]
    protected function withFeaturedProducts(Builder $query): void
    {
        $query->with(['products' => fn (HasMany $q) => $q->where('is_active', true)->where('is_featured', true)]);
    }

    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
    }
}
