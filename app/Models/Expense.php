<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read string $category_label
 * @property-read float $deductible_amount
 *
 * @method static \Database\Factories\ExpenseFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense query()
 *
 * @property-read float|null $total_amount
 *
 * @mixin \Eloquent
 */
class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'amount',
        'category',
        'date',
        'receipt_image',
        'notes',
        'business_percentage',
        'deductible_amount',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
            'business_percentage' => 'integer',
            'deductible_amount' => 'decimal:2',
        ];
    }

    public const CATEGORIES = [
        'supplies' => 'Supplies',
        'ingredients' => 'Ingredients',
        'packaging' => 'Packaging',
        'booth_fees' => 'Booth Fees',
        'delivery' => 'Delivery',
        'marketing' => 'Marketing',
        'insurance' => 'Insurance',
        'education' => 'Education',
        'equipment' => 'Equipment',
        'other' => 'Other',
    ];

    protected function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }

    protected function getDeductibleAmountAttribute(): float
    {
        return round((float) $this->amount * ($this->business_percentage / 100), 2);
    }

    protected static function booted(): void
    {
        static::saving(function (Expense $expense) {
            if ($expense->business_percentage === null) {
                $expense->business_percentage = 100;
            }
            $expense->deductible_amount = $expense->getDeductibleAmountAttribute();
        });
    }
}
