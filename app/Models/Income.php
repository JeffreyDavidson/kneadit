<?php

namespace App\Models;

use App\Builders\IncomeQueryBuilder;
use App\Enums\IncomeSource;
use Database\Factories\IncomeFactory;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property-read string $source_label
 *
 * @method static \Database\Factories\IncomeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income query()
 *
 * @property Carbon|null $date
 * @property IncomeSource $source
 *
 * @mixin \Eloquent
 */
#[UseEloquentBuilder(IncomeQueryBuilder::class)]
class Income extends Model
{
    /** @use HasFactory<IncomeFactory> */
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
            'source' => IncomeSource::class,
        ];
    }

    /** @return Attribute<mixed, never> */
    protected function sourceLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->source->label(),
        );
    }
}
