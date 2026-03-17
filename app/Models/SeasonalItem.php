<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
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

    #[Scope]
    protected function current(Builder $query): void
    {
        $today = Carbon::today();

        $query->where('available_from', '<=', $today)
            ->where('available_until', '>=', $today);
    }

    #[Scope]
    protected function upcoming(Builder $query): void
    {
        $query->where('available_from', '>', Carbon::today());
    }

    #[Scope]
    protected function expired(Builder $query): void
    {
        $query->where('available_until', '<', Carbon::today());
    }

    public function isCurrentlyAvailable(): bool
    {
        $today = Carbon::today();

        return $this->available_from <= $today && $this->available_until >= $today;
    }
}
