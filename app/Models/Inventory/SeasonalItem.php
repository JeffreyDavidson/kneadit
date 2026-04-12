<?php

namespace App\Models\Inventory;

use Database\Factories\Inventory\SeasonalItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

/**
 * @property-read Product|null $product
 *
 * @method static Builder<static>|SeasonalItem current()
 * @method static Builder<static>|SeasonalItem expired()
 * @method static \Database\Factories\SeasonalItemFactory factory($count = null, $state = [])
 * @method static Builder<static>|SeasonalItem newModelQuery()
 * @method static Builder<static>|SeasonalItem newQuery()
 * @method static Builder<static>|SeasonalItem query()
 * @method static Builder<static>|SeasonalItem upcoming()
 *
 * @property Carbon|null $available_from
 * @property Carbon|null $available_until
 *
 * @mixin \Eloquent
 */
#[Fillable('product_id', 'available_from', 'available_until', 'notes')]
class SeasonalItem extends Model
{
    /** @use HasFactory<SeasonalItemFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'available_from' => 'date',
            'available_until' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @param Builder<SeasonalItem> $query */
    #[Scope]
    protected function current(Builder $query): void
    {
        $today = Date::today();

        $query->where('available_from', '<=', $today)
            ->where('available_until', '>=', $today);
    }

    /** @param Builder<SeasonalItem> $query */
    #[Scope]
    protected function upcoming(Builder $query): void
    {
        $query->where('available_from', '>', Date::today());
    }

    /** @param Builder<SeasonalItem> $query */
    #[Scope]
    protected function expired(Builder $query): void
    {
        $query->where('available_until', '<', Date::today());
    }

    /** @return Attribute<bool, never> */
    protected function isCurrentlyAvailable(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->available_from <= Date::today() && $this->available_until >= Date::today(),
        );
    }

    protected static function newFactory(): SeasonalItemFactory
    {
        return SeasonalItemFactory::new();
    }
}
