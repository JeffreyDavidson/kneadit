<?php

namespace App\Models;

use App\Builders\ExpenseQueryBuilder;
use App\Enums\ExpenseCategory;
use Database\Factories\ExpenseFactory;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

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
 * @property Carbon|null $date
 * @property ExpenseCategory $category
 *
 * @mixin \Eloquent
 */
#[UseEloquentBuilder(ExpenseQueryBuilder::class)]
class Expense extends Model
{
    /** @use HasFactory<ExpenseFactory> */
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
            'category' => ExpenseCategory::class,
        ];
    }

    protected function getCategoryLabelAttribute(): string
    {
        return $this->category->label();
    }

    protected function getDeductibleAmountAttribute(): float
    {
        return round((float) $this->amount * ($this->business_percentage / 100), 2);
    }

    protected static function booted(): void
    {
        static::saving(function (Expense $expense) {
            $expense->deductible_amount = $expense->getDeductibleAmountAttribute();
        });
    }
}
