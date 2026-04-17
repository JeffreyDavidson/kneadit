<?php

namespace App\Models\Customers;

use App\Models\Staff\User;
use Database\Factories\Customers\CustomerNoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read User|null $createdBy
 * @property-read Customer|null $customer
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerNote query()
 *
 * @mixin \Eloquent
 */
#[Fillable('customer_id', 'note', 'created_by')]
class CustomerNote extends Model
{
    /** @use HasFactory<CustomerNoteFactory> */
    use HasFactory;

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

    protected static function newFactory(): CustomerNoteFactory
    {
        return CustomerNoteFactory::new();
    }
}
