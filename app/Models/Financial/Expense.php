<?php

namespace App\Models\Financial;

use App\Builders\Financial\ExpenseQueryBuilder;
use App\Casts\MoneyCentsCast;
use App\Casts\PercentageCast;
use App\Enums\Financial\ExpenseCategory;
use App\Observers\Financial\ExpenseObserver;
use Database\Factories\Financial\ExpenseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property \App\ValueObjects\Money $amount
 * @property \App\ValueObjects\Money $deductible_amount
 * @property \App\ValueObjects\Percentage $business_percentage
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
#[UseFactory(ExpenseFactory::class)]
class Expense extends Model
{
    /** @use HasFactory<ExpenseFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => MoneyCentsCast::class,
            'business_percentage' => PercentageCast::class,
            'deductible_amount' => MoneyCentsCast::class,
            'category' => ExpenseCategory::class,
        ];
    }
}
