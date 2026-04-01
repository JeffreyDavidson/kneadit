<?php

namespace App\Models\Operations;

use Carbon\Carbon;
use Database\Factories\Operations\CapacityLimitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property Carbon $date
 * @property Carbon|null $specific_date
 * @property string|null $day_of_week
 * @property int $max_orders
 * @property bool $is_blocked
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Database\Factories\CapacityLimitFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CapacityLimit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CapacityLimit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CapacityLimit query()
 *
 * @mixin \Eloquent
 */
class CapacityLimit extends Model
{
    /** @use HasFactory<CapacityLimitFactory> */
    use HasFactory;

    protected $fillable = [
        'date',
        'specific_date',
        'day_of_week',
        'max_orders',
        'is_blocked',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'max_orders' => 'integer',
            'specific_date' => 'date',
            'is_blocked' => 'boolean',
        ];
    }

    protected static function newFactory(): CapacityLimitFactory
    {
        return CapacityLimitFactory::new();
    }
}
