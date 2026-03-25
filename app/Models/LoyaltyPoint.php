<?php

namespace App\Models;

use App\Enums\LoyaltyPointType;
use Database\Factories\LoyaltyPointFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property LoyaltyPointType $type
 * @property-read Customer|null $customer
 * @property-read Order|null $order
 *
 * @method static Builder<static>|LoyaltyPoint adjusted()
 * @method static Builder<static>|LoyaltyPoint earned()
 * @method static \Database\Factories\LoyaltyPointFactory factory($count = null, $state = [])
 * @method static Builder<static>|LoyaltyPoint forOrder(\App\Models\Order $order)
 * @method static Builder<static>|LoyaltyPoint newModelQuery()
 * @method static Builder<static>|LoyaltyPoint newQuery()
 * @method static Builder<static>|LoyaltyPoint query()
 * @method static Builder<static>|LoyaltyPoint redeemed()
 *
 * @property Carbon|null $created_at
 *
 * @mixin \Eloquent
 */
class LoyaltyPoint extends Model
{
    /** @use HasFactory<LoyaltyPointFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'points',
        'type',
        'description',
        'order_id',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'type' => LoyaltyPointType::class,
        ];
    }

    /** @param Builder<LoyaltyPoint> $query */
    #[Scope]
    protected function earned(Builder $query): void
    {
        $query->where('type', LoyaltyPointType::Earned);
    }

    /** @param Builder<LoyaltyPoint> $query */
    #[Scope]
    protected function redeemed(Builder $query): void
    {
        $query->where('type', LoyaltyPointType::Redeemed);
    }

    /** @param Builder<LoyaltyPoint> $query */
    #[Scope]
    protected function adjusted(Builder $query): void
    {
        $query->where('type', LoyaltyPointType::Adjusted);
    }

    /** @param Builder<LoyaltyPoint> $query */
    #[Scope]
    protected function forOrder(Builder $query, Order $order): void
    {
        $query->where('order_id', $order->id);
    }

    protected static function booted(): void
    {
        static::creating(function (LoyaltyPoint $model) {
            $model->created_at = $model->created_at ?? now();
        });
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
