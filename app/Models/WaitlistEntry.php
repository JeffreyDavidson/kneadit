<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaitlistEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'customer_email',
        'customer_phone',
        'requested_date',
        'product_id',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'requested_date' => 'date',
        ];
    }

    public const STATUSES = [
        'waiting' => 'Waiting',
        'notified' => 'Notified',
        'converted' => 'Converted',
        'removed' => 'Removed',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function scopeWaiting(Builder $query): Builder
    {
        return $query->where('status', 'waiting');
    }

    public function scopeForDate(Builder $query, Carbon|string $date): Builder
    {
        return $query->whereDate('requested_date', Carbon::parse($date));
    }

    public function markNotified(): void
    {
        $this->update([
            'status' => 'notified',
        ]);
    }

    public function markConverted(): void
    {
        $this->update([
            'status' => 'converted',
        ]);
    }

    public function markRemoved(): void
    {
        $this->update([
            'status' => 'removed',
        ]);
    }
}
