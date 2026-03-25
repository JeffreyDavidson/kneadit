<?php

namespace App\Models;

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
 * @mixin \Eloquent
 */
class OrderMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'sender_type',
        'sender_name',
        'message',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
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
        return $this->sender_type === 'baker';
    }

    public function isCustomer(): bool
    {
        return $this->sender_type === 'customer';
    }
}
