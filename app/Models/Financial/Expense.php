<?php

namespace App\Models\Financial;

use App\Builders\Financial\ExpenseQueryBuilder;
use App\Enums\Financial\ExpenseCategory;
use App\Observers\Financial\ExpenseObserver;
use Database\Factories\Financial\ExpenseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property-read string $category_label
 * @property float $deductible_amount
 *
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
#[Fillable('description', 'amount', 'category', 'date', 'receipt_image', 'notes', 'business_percentage', 'deductible_amount')]
#[UseEloquentBuilder(ExpenseQueryBuilder::class)]
#[ObservedBy(ExpenseObserver::class)]
class Expense extends Model
{
    /** @use HasFactory<ExpenseFactory> */
    use HasFactory;

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

    /** @return Attribute<mixed, never> */
    protected function categoryLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->category->getLabel(),
        );
    }

    protected static function newFactory(): ExpenseFactory
    {
        return ExpenseFactory::new();
    }
}
