<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
