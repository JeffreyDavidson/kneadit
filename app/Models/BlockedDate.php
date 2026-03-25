<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'reason',
        'is_all_day',
        'open_time',
        'close_time',
    ];

    protected $casts = [
        'date' => 'date',
        'is_all_day' => 'boolean',
    ];

    public static function isBlocked(Carbon $date): bool
    {
        return static::where('date', $date->toDateString())
            ->where('is_all_day', true)
            ->exists();
    }

    public static function getBlockedReason(Carbon $date): ?string
    {
        return static::where('date', $date->toDateString())
            ->where('is_all_day', true)
            ->value('reason');
    }
}
