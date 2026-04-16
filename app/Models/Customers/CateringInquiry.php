<?php

namespace App\Models\Customers;

use App\Casts\PhoneNumberCast;
use App\Casts\StripTagsCast;
use App\Enums\Customers\CateringInquiryStatus;
use App\Models\Concerns\LogsActivity;
use Database\Factories\Customers\CateringInquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property CateringInquiryStatus $status
 * @property string $event_type
 * @property-read string $event_type_label
 * @property-read string $status_label
 *
 * @method static \Database\Factories\CateringInquiryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CateringInquiry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CateringInquiry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CateringInquiry query()
 *
 * @mixin \Eloquent
 */
#[Fillable('customer_name', 'customer_email', 'customer_phone', 'event_type', 'event_date', 'guest_count', 'budget', 'details', 'dietary_requirements', 'venue_address', 'status', 'quoted_amount', 'notes')]
class CateringInquiry extends Model
{
    /** @use HasFactory<CateringInquiryFactory> */
    use HasFactory;

    use LogsActivity;

    protected function casts(): array
    {
        return [
            'status' => CateringInquiryStatus::class,
            'event_date' => 'date',
            'guest_count' => 'integer',
            'budget' => 'decimal:2',
            'quoted_amount' => 'decimal:2',
            'customer_phone' => PhoneNumberCast::class,
            'details' => StripTagsCast::class,
            'dietary_requirements' => StripTagsCast::class,
            'venue_address' => StripTagsCast::class,
        ];
    }

    /** @return Attribute<mixed, never> */
    protected function eventTypeLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => (string) $this->event_type,
        );
    }

    /** @return Attribute<mixed, never> */
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status->getLabel(),
        );
    }

    protected static function newFactory(): CateringInquiryFactory
    {
        return CateringInquiryFactory::new();
    }
}
