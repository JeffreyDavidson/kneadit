<?php

namespace App\Models;

use Database\Factories\LoyaltyRewardFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $reward_type_label
 * @property-read Product|null $product
 *
 * @method static Builder<static>|LoyaltyReward active()
 * @method static \Database\Factories\LoyaltyRewardFactory factory($count = null, $state = [])
 * @method static Builder<static>|LoyaltyReward newModelQuery()
 * @method static Builder<static>|LoyaltyReward newQuery()
 * @method static Builder<static>|LoyaltyReward query()
 *
 * @mixin \Eloquent
 */
class LoyaltyReward extends Model
{
    /** @use HasFactory<LoyaltyRewardFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'points_required',
        'reward_type',
        'reward_value',
        'product_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'reward_value' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @param Builder<LoyaltyReward> $query */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /** @return Attribute<mixed, never> */
    protected function rewardTypeLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->reward_type) {
                'percentage_discount' => $this->reward_value.'% Off',
                'fixed_discount' => '$'.number_format((float) $this->reward_value, 2).' Off',
                'free_product' => 'Free '.($this->product->name ?? 'Product'),
                default => $this->reward_type,
            },
        );
    }
}
