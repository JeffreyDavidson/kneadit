<?php

namespace App\Models\Financial;

use App\Enums\Financial\GiftCardTransactionType;
use App\Models\Orders\Order;
use Database\Factories\Financial\GiftCardTransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property-read GiftCard|null $giftCard
 * @property-read Order|null $order
 *
 * @method static \Database\Factories\GiftCardTransactionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GiftCardTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GiftCardTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GiftCardTransaction query()
 *
 * @property Carbon|null $created_at
 *
 * @mixin \Eloquent
 */
#[WithoutTimestamps]
#[Fillable('gift_card_id', 'amount', 'type', 'order_id', 'notes', 'created_at')]
class GiftCardTransaction extends Model
{
    /** @use HasFactory<GiftCardTransactionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'created_at' => 'datetime',
            'type' => GiftCardTransactionType::class,
        ];
    }

    /**
     * @return BelongsTo<GiftCard, $this>
     */
    public function giftCard(): BelongsTo
    {
        return $this->belongsTo(GiftCard::class);
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    protected static function newFactory(): GiftCardTransactionFactory
    {
        return GiftCardTransactionFactory::new();
    }
}
