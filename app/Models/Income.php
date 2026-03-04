<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function getSourceLabelAttribute(): string
    {
        return self::SOURCES[$this->source] ?? ucfirst($this->source);
    }
}