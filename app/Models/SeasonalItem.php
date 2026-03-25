<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
 * @mixin \Eloquent
 */
class SeasonalItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'available_from',
        'available_until',
        'notes',
    ];

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

    #[Scope]
    protected function current(Builder $query): void
    {
        $today = Date::today();

        $query->where('available_from', '<=', $today)
            ->where('available_until', '>=', $today);
    }

    #[Scope]
    protected function upcoming(Builder $query): void
    {
        $query->where('available_from', '>', Date::today());
    }

    #[Scope]
    protected function expired(Builder $query): void
    {
        $query->where('available_until', '<', Date::today());
    }

    public function isCurrentlyAvailable(): bool
    {
        $today = Date::today();

        return $this->available_from <= $today && $this->available_until >= $today;
    }
}
