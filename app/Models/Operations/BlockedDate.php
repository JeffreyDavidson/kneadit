<?php

namespace App\Models\Operations;

use Database\Factories\Operations\BlockedDateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @method static \Database\Factories\BlockedDateFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlockedDate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlockedDate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlockedDate query()
 *
 * @property Carbon $date
 *
 * @mixin \Eloquent
 */
class BlockedDate extends Model
{
    /** @use HasFactory<BlockedDateFactory> */
    use HasFactory;

    protected $fillable = [
        'date',
        'reason',
        'is_all_day',
        'open_time',
        'close_time',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_all_day' => 'boolean',
        ];
    }

    protected static function newFactory(): BlockedDateFactory
    {
        return BlockedDateFactory::new();
    }
}
