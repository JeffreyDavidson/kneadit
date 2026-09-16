<?php

namespace App\Models\Operations;

use Database\Factories\Operations\ScheduledNotificationRunFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $notification_key
 * @property Carbon $claimed_at
 *
 * @mixin \Eloquent
 */
#[Fillable('notification_key', 'claimed_at')]
#[UseFactory(ScheduledNotificationRunFactory::class)]
class ScheduledNotificationRun extends Model
{
    /** @use HasFactory<ScheduledNotificationRunFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'claimed_at' => 'datetime',
        ];
    }
}
