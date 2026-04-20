<?php

namespace App\Models\Engagement;

use App\Builders\Engagement\LoyaltyRewardQueryBuilder;
use App\Casts\MoneyCast;
use App\Casts\PercentageCast;
use App\Enums\Engagement\RewardType;
use App\Models\Inventory\Product;
use App\Presenters\LoyaltyRewardPresenter;
use Database\Factories\Engagement\LoyaltyRewardFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property RewardType $reward_type
 * @property-read string $reward_type_label
 * @property-read Product|null $product
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoyaltyReward newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoyaltyReward newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoyaltyReward query()
 *
 * @property \App\ValueObjects\Money|null $discount_amount
 * @property \App\ValueObjects\Percentage|null $discount_percentage
 *
 * @mixin \Eloquent
 */
#[Fillable('name', 'description', 'points_required', 'reward_type', 'discount_amount', 'discount_percentage', 'product_id', 'is_active')]
#[UseEloquentBuilder(LoyaltyRewardQueryBuilder::class)]
class LoyaltyReward extends Model
{
    /** @use HasFactory<LoyaltyRewardFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'points_required' => 'integer',
            'discount_amount' => MoneyCast::class,
            'discount_percentage' => PercentageCast::class,
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
