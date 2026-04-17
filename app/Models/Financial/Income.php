<?php

namespace App\Models\Financial;

use App\Builders\Financial\IncomeQueryBuilder;
use App\Enums\Financial\IncomeSource;
use Database\Factories\Financial\IncomeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property-read string $source_label
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income query()
 *
 * @property Carbon|null $date
 * @property IncomeSource $source
 *
 * @mixin \Eloquent
 */
#[Fillable('description', 'amount', 'source', 'date', 'notes')]
#[UseEloquentBuilder(IncomeQueryBuilder::class)]
class Income extends Model
{
    /** @use HasFactory<IncomeFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
            'source' => IncomeSource::class,
        ];
    }

    /** @return Attribute<mixed, never> */
    protected function sourceLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->source->getLabel(),
        );
    }

    protected static function newFactory(): IncomeFactory
    {
        return IncomeFactory::new();
    }
}
