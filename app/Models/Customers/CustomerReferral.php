<?php

namespace App\Models\Customers;

use App\Enums\Customers\CustomerReferralStatus;
use App\Models\Financial\Coupon;
use App\Models\Orders\Order;
use Database\Factories\Customers\CustomerReferralFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $referrer_customer_id
 * @property int|null $referred_customer_id
 * @property int|null $order_id
 * @property int|null $reward_coupon_id
 * @property CustomerReferralStatus $status
 * @property Carbon|null $completed_at
 * @property-read Customer $referrer
 * @property-read Customer|null $referred
 * @property-read Order|null $order
 * @property-read Coupon|null $rewardCoupon
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerReferral newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerReferral newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerReferral query()
 *
 * @mixin \Eloquent
 */
#[Fillable('referrer_customer_id', 'referred_customer_id', 'order_id', 'reward_coupon_id', 'status', 'completed_at')]
#[UseFactory(CustomerReferralFactory::class)]
class CustomerReferral extends Model
{
    /** @use HasFactory<CustomerReferralFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => CustomerReferralStatus::class,
            'completed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Customer, $this> */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'referrer_customer_id');
    }

    /** @return BelongsTo<Customer, $this> */
    public function referred(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'referred_customer_id');
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** @return BelongsTo<Coupon, $this> */
    public function rewardCoupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'reward_coupon_id');
    }
}
