<?php

namespace App\Models\Financial;

use App\Enums\Financial\CouponTransactionType;
use App\Models\Orders\Order;
use Database\Factories\Financial\CouponTransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property-read Coupon|null $coupon
 * @property-read Order|null $order
 * @property Carbon|null $created_at
 *
 * @mixin \Eloquent
 */
#[WithoutTimestamps]
#[Fillable('coupon_id', 'amount', 'type', 'order_id', 'notes', 'created_at')]
class CouponTransaction extends Model
{
    /** @use HasFactory<CouponTransactionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'created_at' => 'datetime',
            'type' => CouponTransactionType::class,
        ];
    }

    /**
     * @return BelongsTo<Coupon, $this>
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    protected static function newFactory(): CouponTransactionFactory
    {
        return CouponTransactionFactory::new();
    }
}
