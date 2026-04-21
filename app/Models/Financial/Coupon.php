<?php

namespace App\Models\Financial;

use App\Builders\Financial\CouponQueryBuilder;
use App\Casts\MoneyCast;
use App\Casts\PercentageCast;
use App\Enums\Financial\CouponType;
use App\Models\Orders\Order;
use App\Observers\Engagement\CouponObserver;
use App\Observers\LogsActivityObserver;
use Database\Factories\Financial\CouponFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property CouponType $type
 * @property-read Collection<int, CouponTransaction> $transactions
 * @property-read int|null $transactions_count
 * @property-read Collection<int, Order> $orders
 * @property-read int|null $orders_count
 *
 * @method static CouponQueryBuilder|Coupon newModelQuery()
 * @method static CouponQueryBuilder|Coupon newQuery()
 * @method static CouponQueryBuilder|Coupon query()
 *
 * @property Carbon|null $starts_at
 * @property Carbon|null $expires_at
 * @property \App\ValueObjects\Money|null $fixed_amount
 * @property \App\ValueObjects\Percentage|null $percentage
 * @property \App\ValueObjects\Money|null $min_order_amount
 *
 * @mixin \Eloquent
 */
#[Fillable('code', 'type', 'fixed_amount', 'percentage', 'min_order_amount', 'max_uses', 'used_count', 'starts_at', 'expires_at', 'is_active')]
#[ObservedBy([CouponObserver::class, LogsActivityObserver::class])]
#[UseEloquentBuilder(CouponQueryBuilder::class)]
#[UseFactory(CouponFactory::class)]
class Coupon extends Model
{
    /** @use HasFactory<CouponFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'fixed_amount' => MoneyCast::class,
            'percentage' => PercentageCast::class,
            'min_order_amount' => MoneyCast::class,
            'max_uses' => 'integer',
            'used_count' => 'integer',
            'type' => CouponType::class,
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return HasMany<CouponTransaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(CouponTransaction::class);
    }
}
