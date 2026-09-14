<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $notification_key
 * @property \Illuminate\Support\Carbon $claimed_at
 *
 * @mixin \Eloquent
 */
#[Fillable('notification_key', 'claimed_at')]
class ScheduledNotificationRun extends Model
{
    protected function casts(): array
    {
        return [
            'claimed_at' => 'datetime',
        ];
    }
}
