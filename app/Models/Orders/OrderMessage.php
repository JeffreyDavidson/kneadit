<?php

namespace App\Models\Orders;

use App\Enums\Orders\SenderType;
use Database\Factories\Orders\OrderMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read Order|null $order
 *
 * @method static \Database\Factories\OrderMessageFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderMessage query()
 *
 * @property SenderType $sender_type
 *
 * @mixin \Eloquent
 */
#[Fillable('order_id', 'sender_type', 'sender_name', 'message', 'is_read')]
class OrderMessage extends Model
{
    /** @use HasFactory<OrderMessageFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'sender_type' => SenderType::class,
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isBaker(): bool
    {
        return $this->sender_type->isBaker();
    }

    public function isCustomer(): bool
    {
        return $this->sender_type->isCustomer();
    }

    protected static function newFactory(): OrderMessageFactory
    {
        return OrderMessageFactory::new();
    }
}
