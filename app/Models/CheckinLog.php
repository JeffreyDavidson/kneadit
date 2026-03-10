<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckinLog extends Model
{
    protected $connection = 'central';

    protected $fillable = [
        'checkin_id',
        'tenant_id',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function checkin(): BelongsTo
    {
        return $this->belongsTo(ScheduledCheckin::class, 'checkin_id');
    }
}
