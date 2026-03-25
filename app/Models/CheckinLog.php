<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckinLog extends Model
{
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
