<?php

namespace App\Models\Customers;

use App\Builders\Customers\CateringInquiryQueryBuilder;
use App\Casts\MoneyCentsCast;
use App\Casts\PhoneNumberCast;
use App\Casts\StripTagsCast;
use App\Enums\Customers\CateringInquiryStatus;
use App\Models\Orders\Order;
use App\Observers\LogsActivityObserver;
use Database\Factories\Customers\CateringInquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property CateringInquiryStatus $status
 * @property string $event_type
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CateringInquiry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CateringInquiry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CateringInquiry query()
 *
 * @property \App\ValueObjects\Money|null $budget
 * @property \App\ValueObjects\Money|null $quoted_amount
 * @property \App\ValueObjects\Money|null $deposit_amount
 * @property \Illuminate\Support\Carbon|null $event_date
 * @property \Illuminate\Support\Carbon|null $deposit_paid_at
 * @property string|null $deposit_reference
 * @property string|null $stripe_checkout_session_id
 *
 * @mixin \Eloquent
 */
#[Fillable('customer_name', 'customer_email', 'customer_phone', 'event_type', 'event_date', 'guest_count', 'budget', 'details', 'dietary_requirements', 'venue_address', 'status', 'quoted_amount', 'deposit_amount', 'deposit_paid_at', 'deposit_reference', 'stripe_checkout_session_id', 'notes')]
#[ObservedBy(LogsActivityObserver::class)]
#[UseEloquentBuilder(CateringInquiryQueryBuilder::class)]
#[UseFactory(CateringInquiryFactory::class)]
class CateringInquiry extends Model
{
    /** @use HasFactory<CateringInquiryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => CateringInquiryStatus::class,
            'event_date' => 'date',
            'guest_count' => 'integer',
            'budget' => MoneyCentsCast::class,
            'quoted_amount' => MoneyCentsCast::class,
            'deposit_amount' => MoneyCentsCast::class,
            'deposit_paid_at' => 'datetime',
            'customer_phone' => PhoneNumberCast::class,
            'details' => StripTagsCast::class,
            'dietary_requirements' => StripTagsCast::class,
            'venue_address' => StripTagsCast::class,
        ];
    }

    /**
     * @return HasOne<Order, $this>
     */
    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    /**
     * @return HasMany<CateringInquiryItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(CateringInquiryItem::class)->orderBy('sort_order')->orderBy('id');
    }
}
