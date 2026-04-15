<?php

namespace App\Models\Engagement;

use App\Enums\Engagement\RewardType;
use App\Models\Inventory\Product;
use App\Presenters\LoyaltyRewardPresenter;
use Database\Factories\Engagement\LoyaltyRewardFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property RewardType $reward_type
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
#[Fillable('name', 'description', 'points_required', 'reward_type', 'reward_value', 'product_id', 'is_active')]
class LoyaltyReward extends Model
{
    /** @use HasFactory<LoyaltyRewardFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'points_required' => 'integer',
            'reward_value' => 'decimal:2',
            'is_active' => 'boolean',
            'reward_type' => RewardType::class,
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

    /** @return Attribute<string, never> */
    protected function rewardTypeLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => (new LoyaltyRewardPresenter($this))->rewardTypeLabel(),
        );
    }

    protected static function newFactory(): LoyaltyRewardFactory
    {
        return LoyaltyRewardFactory::new();
    }
}
