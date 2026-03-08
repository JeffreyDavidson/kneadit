<?php

namespace App\Models;

use App\Traits\LogsActivity;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GiftCard extends Model
{
    use LogsActivity;

    protected $fillable = [
        'code',
        'initial_balance',
        'current_balance',
        'purchaser_name',
        'purchaser_email',
        'recipient_name',
        'recipient_email',
        'message',
        'is_active',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'initial_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'is_active' => 'boolean',
            'expires_at' => 'date',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(GiftCardTransaction::class);
    }

    public function isUsable(): bool
    {
        return $this->is_active
            && $this->current_balance > 0
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function getStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'expired';
        }
        if ((float) $this->current_balance <= 0) {
            return 'depleted';
        }
        return 'active';
    }
}
