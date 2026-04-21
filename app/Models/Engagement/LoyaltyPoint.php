<?php

namespace App\Models\Engagement;

use App\Builders\Engagement\LoyaltyPointQueryBuilder;
use App\Enums\Engagement\LoyaltyPointType;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Observers\Engagement\LoyaltyPointObserver;
use Database\Factories\Engagement\LoyaltyPointFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property LoyaltyPointType $type
 * @property-read Customer|null $customer
 * @property-read Order|null $order
 *
 * @method static LoyaltyPointQueryBuilder|LoyaltyPoint adjusted()
 * @method static LoyaltyPointQueryBuilder|LoyaltyPoint earned()
 * @method static LoyaltyPointQueryBuilder|LoyaltyPoint forOrder(\App\Models\Orders\Order $order)
 * @method static LoyaltyPointQueryBuilder|LoyaltyPoint newModelQuery()
 * @method static LoyaltyPointQueryBuilder|LoyaltyPoint newQuery()
 * @method static LoyaltyPointQueryBuilder|LoyaltyPoint query()
 * @method static LoyaltyPointQueryBuilder|LoyaltyPoint redeemed()
 *
 * @property Carbon|null $created_at
 *
 * @mixin \Eloquent
 */
#[WithoutTimestamps]
#[Fillable('customer_id', 'points', 'type', 'description', 'order_id')]
#[ObservedBy(LoyaltyPointObserver::class)]
#[UseEloquentBuilder(LoyaltyPointQueryBuilder::class)]
#[UseFactory(LoyaltyPointFactory::class)]
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
