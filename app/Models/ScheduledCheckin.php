<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScheduledCheckin extends Model
{
    protected $connection = 'central';

    protected $fillable = [
        'name',
        'days_after_signup',
        'subject',
        'body',
        'is_active',
    ];

    protected $casts = [
        'days_after_signup' => 'integer',
        'is_active' => 'boolean',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(CheckinLog::class, 'checkin_id');
    }
}
