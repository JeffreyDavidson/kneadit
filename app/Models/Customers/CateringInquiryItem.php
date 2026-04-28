<?php

namespace App\Models\Customers;

use App\Casts\MoneyCentsCast;
use App\Observers\Customers\CateringInquiryItemObserver;
use App\ValueObjects\Money;
use Database\Factories\Customers\CateringInquiryItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $catering_inquiry_id
 * @property string $name
 * @property int $quantity
 * @property Money $unit_price
 * @property ?string $special_instructions
 * @property int $sort_order
 * @property-read CateringInquiry $inquiry
 * @property-read Money $line_total
 *
 * @mixin \Eloquent
 */
#[Fillable('catering_inquiry_id', 'name', 'quantity', 'unit_price', 'special_instructions', 'sort_order')]
#[ObservedBy(CateringInquiryItemObserver::class)]
#[UseFactory(CateringInquiryItemFactory::class)]
class CateringInquiryItem extends Model
{
    /** @use HasFactory<CateringInquiryItemFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'unit_price' => MoneyCentsCast::class,
            'quantity' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<CateringInquiry, $this>
     */
    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(CateringInquiry::class, 'catering_inquiry_id');
    }

    /**
     * @return Attribute<Money, never>
     */
    protected function lineTotal(): Attribute
    {
        return Attribute::get(fn (): Money => Money::fromCents($this->unit_price->cents() * $this->quantity));
    }
}
