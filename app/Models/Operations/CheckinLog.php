<?php

namespace App\Models\Operations;

use Database\Factories\Operations\CheckinLogFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $checkin_id
 * @property string $tenant_id
 * @property Carbon|null $sent_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ScheduledCheckin $checkin
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckinLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckinLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckinLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckinLog whereCheckinId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckinLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckinLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckinLog whereSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckinLog whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckinLog whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Connection('central')]
#[Fillable('checkin_id', 'tenant_id', 'sent_at')]
#[UseFactory(CheckinLogFactory::class)]
class CheckinLog extends Model
{
    /** @use HasFactory<CheckinLogFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<ScheduledCheckin, $this>
     */
    public function checkin(): BelongsTo
    {
        return $this->belongsTo(ScheduledCheckin::class, 'checkin_id');
    }
}
