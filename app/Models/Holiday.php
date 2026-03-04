<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Holiday extends Model
{
    protected $fillable = [
        'name',
        'date',
        'lead_days',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function getDaysAwayAttribute(): int
    {
        return now()->diffInDays($this->date, false);
    }

    public function getStartPrepByAttribute(): Carbon
    {
        return $this->date->subDays($this->lead_days);
    }

    public function getIsUpcomingAttribute(): bool
    {
        return $this->date->isFuture();
    }

    public function getIsInPrepPeriodAttribute(): bool
    {
        return now()->isAfter($this->start_prep_by) && $this->date->isFuture();
    }
}
