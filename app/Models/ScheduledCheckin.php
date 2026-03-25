<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property int $days_after_signup
 * @property string $subject
 * @property string $body
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, CheckinLog> $logs
 * @property-read int|null $logs_count
 *
 * @method static \Database\Factories\ScheduledCheckinFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledCheckin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledCheckin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledCheckin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledCheckin whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledCheckin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledCheckin whereDaysAfterSignup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledCheckin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledCheckin whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledCheckin whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledCheckin whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledCheckin whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ScheduledCheckin extends Model
{
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'name',
        'days_after_signup',
        'subject',
        'body',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'days_after_signup' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<CheckinLog, $this>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(CheckinLog::class, 'checkin_id');
    }
}
