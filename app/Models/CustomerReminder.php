<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerReminder extends Model
{
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
