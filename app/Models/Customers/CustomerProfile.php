<?php

namespace App\Models\Customers;

use Database\Factories\Customers\CustomerProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read Customer|null $customer
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfile query()
 *
 * @mixin \Eloquent
 */
#[Fillable('customer_id', 'birthday', 'notes')]
class CustomerProfile extends Model
{
    /** @use HasFactory<CustomerProfileFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'birthday' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    protected static function newFactory(): CustomerProfileFactory
    {
        return CustomerProfileFactory::new();
    }
}
