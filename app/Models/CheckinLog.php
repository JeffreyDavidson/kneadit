<?php

namespace App\Models;

use Database\Factories\CheckinLogFactory;
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
 * @method static \Database\Factories\CheckinLogFactory factory($count = null, $state = [])
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
class CheckinLog extends Model
{
    /** @use HasFactory<CheckinLogFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'checkin_id',
        'tenant_id',
        'sent_at',
    ];

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
