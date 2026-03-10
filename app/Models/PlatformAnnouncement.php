<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PlatformAnnouncement extends Model
{
    protected $connection = 'central';

    protected $fillable = [
        'title',
        'body',
        'type',
        'target_plans',
        'is_active',
        'starts_at',
        'ends_at',
        'is_dismissable',
    ];

    protected $casts = [
        'target_plans' => 'array',
        'is_active' => 'boolean',
        'is_dismissable' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }
}
