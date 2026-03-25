<?php

namespace App\Models;

use Database\Factories\CustomerNoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read User|null $createdBy
 * @property-read Customer|null $customer
 *
 * @method static \Database\Factories\CustomerNoteFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerNote query()
 *
 * @mixin \Eloquent
 */
class CustomerNote extends Model
{
    /** @use HasFactory<CustomerNoteFactory> */
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'note',
        'created_by',
    ];

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
