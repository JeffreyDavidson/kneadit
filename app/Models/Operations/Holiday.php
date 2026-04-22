<?php

namespace App\Models\Operations;

use App\Builders\Operations\HolidayQueryBuilder;
use Database\Factories\Operations\HolidayFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property Carbon $date
 * @property int $lead_days
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $order_deadline
 * @property Carbon|null $prep_start
 * @property int|null $max_orders
 * @property bool $is_active
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday query()
 *
 * @mixin \Eloquent
 */
#[Fillable('name', 'date', 'lead_days', 'order_deadline', 'prep_start', 'max_orders', 'notes', 'is_active')]
#[UseEloquentBuilder(HolidayQueryBuilder::class)]
#[UseFactory(HolidayFactory::class)]
class Holiday extends Model
{
    /** @use HasFactory<HolidayFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'lead_days' => 'integer',
            'order_deadline' => 'date',
            'prep_start' => 'date',
            'max_orders' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
