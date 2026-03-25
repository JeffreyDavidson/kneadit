<?php

namespace App\Models;

use Database\Factories\CustomerReminderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $next_reminder_date
 * @property-read Customer|null $customer
 *
 * @method static \Database\Factories\CustomerReminderFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerReminder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerReminder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerReminder query()
 *
 * @mixin \Eloquent
 */
class CustomerReminder extends Model
{
    /** @use HasFactory<CustomerReminderFactory> */
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'last_order_date',
        'reminder_sent_at',
        'next_reminder_date',
    ];

    protected function casts(): array
    {
        return [
            'last_order_date' => 'date',
            'reminder_sent_at' => 'datetime',
            'next_reminder_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
