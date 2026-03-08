<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeasonalItem extends Model
{
    protected $fillable = [
        'product_id',
        'available_from',
        'available_until',
        'notes',
    ];

    protected $casts = [
        'available_from' => 'date',
        'available_until' => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeCurrent($query)
    {
        $today = Carbon::today();
        return $query->where('available_from', '<=', $today)
            ->where('available_until', '>=', $today);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('available_from', '>', Carbon::today());
    }

    public function scopeExpired($query)
    {
        return $query->where('available_until', '<', Carbon::today());
    }

    public function isCurrentlyAvailable(): bool
    {
        $today = Carbon::today();
        return $this->available_from <= $today && $this->available_until >= $today;
    }
}
