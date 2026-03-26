<?php

namespace App\Models;

use App\Enums\CouponType;
use App\Observers\CouponObserver;
use App\Traits\LogsActivity;
use Database\Factories\CouponFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property CouponType $type
 * @property-read Collection<int, Order> $orders
 * @property-read int|null $orders_count
 *
 * @method static \Database\Factories\CouponFactory factory($count = null, $state = [])
 * @method static Builder<static>|Coupon newModelQuery()
 * @method static Builder<static>|Coupon newQuery()
 * @method static Builder<static>|Coupon query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon valid()
 *
 * @property Carbon|null $starts_at
 * @property Carbon|null $expires_at
 *
 * @mixin \Eloquent
 */
#[ObservedBy(CouponObserver::class)]
class Coupon extends Model
{
    /** @use HasFactory<CouponFactory> */
    use HasFactory, LogsActivity;

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_amount',
        'max_uses',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
    ];

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

    /** @param Builder<Coupon> $query */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /** @param Builder<Coupon> $query */
    #[Scope]
    protected function valid(Builder $query): void
    {
        $query->active()
            ->where(function (Builder $q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->where(function (Builder $q) {
                $q->whereNull('max_uses')->orWhereColumn('used_count', '<', 'max_uses');
            });
    }

    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($this->min_order_amount && $subtotal < (float) $this->min_order_amount) {
            return 0;
        }

        if ($this->type === CouponType::Percentage) {
            return round($subtotal * ((float) $this->value / 100), 2);
        }

        // fixed amount — can't discount more than the subtotal
        return round(min((float) $this->value, $subtotal), 2);
    }
}
