<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read string $source_label
 *
 * @method static \Database\Factories\IncomeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income query()
 *
 * @mixin \Eloquent
 */
class Income extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'amount',
        'source',
        'date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public const SOURCES = [
        'farmers_market' => 'Farmers Market',
        'cash_sale' => 'Cash Sale',
        'paypal_direct' => 'PayPal Direct',
        'catering' => 'Catering',
        'other' => 'Other',
    ];

    protected function getSourceLabelAttribute(): string
    {
        return self::SOURCES[$this->source];
    }
}
