<?php

namespace App\Models\Financial;

use App\Builders\Financial\IncomeQueryBuilder;
use App\Casts\MoneyCentsCast;
use App\Enums\Financial\IncomeSource;
use Database\Factories\Financial\IncomeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income query()
 *
 * @property Carbon|null $date
 * @property IncomeSource $source
 * @property \App\ValueObjects\Money $amount
 *
 * @mixin \Eloquent
 */
#[Fillable('description', 'amount', 'source', 'date', 'notes')]
#[UseEloquentBuilder(IncomeQueryBuilder::class)]
#[UseFactory(IncomeFactory::class)]
class Income extends Model
{
    /** @use HasFactory<IncomeFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => MoneyCentsCast::class,
            'source' => IncomeSource::class,
        ];
    }
}
