<?php

namespace App\Models\Customers;

use App\Builders\Customers\CateringInquiryQueryBuilder;
use App\Casts\MoneyCast;
use App\Casts\PhoneNumberCast;
use App\Casts\StripTagsCast;
use App\Enums\Customers\CateringInquiryStatus;
use App\Observers\LogsActivityObserver;
use Database\Factories\Customers\CateringInquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
 *
 * @mixin \Eloquent
 */
#[Fillable('customer_name', 'customer_email', 'customer_phone', 'event_type', 'event_date', 'guest_count', 'budget', 'details', 'dietary_requirements', 'venue_address', 'status', 'quoted_amount', 'notes')]
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
            'budget' => MoneyCast::class,
            'quoted_amount' => MoneyCast::class,
            'customer_phone' => PhoneNumberCast::class,
            'details' => StripTagsCast::class,
            'dietary_requirements' => StripTagsCast::class,
            'venue_address' => StripTagsCast::class,
        ];
    }
}
