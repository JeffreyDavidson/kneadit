<?php

namespace App\Models\Engagement;

use App\Enums\Engagement\LoyaltyPointType;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Observers\Engagement\LoyaltyPointObserver;
use Database\Factories\Engagement\LoyaltyPointFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
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
 * @method static Builder<static>|LoyaltyPoint forOrder(\App\Models\Orders\Order $order)
 * @method static Builder<static>|LoyaltyPoint newModelQuery()
 * @method static Builder<static>|LoyaltyPoint newQuery()
 * @method static Builder<static>|LoyaltyPoint query()
 * @method static Builder<static>|LoyaltyPoint redeemed()
 *
 * @property Carbon|null $created_at
 *
 * @mixin \Eloquent
 */
#[WithoutTimestamps]
#[Fillable('customer_id', 'points', 'type', 'description', 'order_id')]
#[ObservedBy(LoyaltyPointObserver::class)]
class LoyaltyPoint extends Model
{
    /** @use HasFactory<LoyaltyPointFactory> */
    use HasFactory;

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

    protected static function newFactory(): LoyaltyPointFactory
    {
        return LoyaltyPointFactory::new();
    }
}
