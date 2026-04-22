<?php

namespace App\Models\Operations;

use Database\Factories\Operations\ScheduledCheckinFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledCheckin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledCheckin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledCheckin query()
 *
 * @mixin \Eloquent
 */
#[Connection('central')]
#[Fillable('name', 'days_after_signup', 'subject', 'body', 'is_active')]
#[UseFactory(ScheduledCheckinFactory::class)]
class ScheduledCheckin extends Model
{
    /** @use HasFactory<ScheduledCheckinFactory> */
    use HasFactory;

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
