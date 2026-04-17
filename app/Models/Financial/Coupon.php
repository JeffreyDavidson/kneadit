<?php

namespace App\Models\Financial;

use App\Builders\Financial\CouponQueryBuilder;
use App\Enums\Financial\CouponType;
use App\Models\Orders\Order;
use App\Observers\Engagement\CouponObserver;
use App\Observers\LogsActivityObserver;
use Database\Factories\Financial\CouponFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
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
 *
 * @mixin \Eloquent
 */
#[Fillable('code', 'type', 'value', 'min_order_amount', 'max_uses', 'used_count', 'starts_at', 'expires_at', 'is_active')]
#[ObservedBy([CouponObserver::class, LogsActivityObserver::class])]
#[UseEloquentBuilder(CouponQueryBuilder::class)]
class Coupon extends Model
{
    /** @use HasFactory<CouponFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
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

    protected static function newFactory(): CouponFactory
    {
        return CouponFactory::new();
    }
}
