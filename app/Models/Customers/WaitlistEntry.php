<?php

namespace App\Models\Customers;

use App\Builders\Customers\WaitlistEntryQueryBuilder;
use App\Casts\PhoneNumberCast;
use App\Casts\StripTagsCast;
use App\Enums\Customers\WaitlistStatus;
use App\Models\Inventory\Product;
use Database\Factories\Customers\WaitlistEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property WaitlistStatus $status
 * @property-read string $status_label
 * @property-read Product|null $product
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WaitlistEntry forDate(\Carbon\Carbon|string $date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WaitlistEntry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WaitlistEntry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WaitlistEntry query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WaitlistEntry waiting()
 *
 * @mixin \Eloquent
 */
#[Fillable('customer_name', 'customer_email', 'customer_phone', 'requested_date', 'product_id', 'notes', 'status')]
#[UseEloquentBuilder(WaitlistEntryQueryBuilder::class)]
#[UseFactory(WaitlistEntryFactory::class)]
class WaitlistEntry extends Model
{
    /** @use HasFactory<WaitlistEntryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'requested_date' => 'date',
            'status' => WaitlistStatus::class,
            'notes' => StripTagsCast::class,
            'customer_phone' => PhoneNumberCast::class,
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return Attribute<string, never> */
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status->getLabel(),
        );
    }
}
