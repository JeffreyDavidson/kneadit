<?php

namespace App\Models;

use App\Enums\GiftCardStatus;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read GiftCardStatus $status
 * @property-read Collection<int, GiftCardTransaction> $transactions
 * @property-read int|null $transactions_count
 *
 * @method static \Database\Factories\GiftCardFactory factory($count = null, $state = [])
 * @method static Builder<static>|GiftCard newModelQuery()
 * @method static Builder<static>|GiftCard newQuery()
 * @method static Builder<static>|GiftCard query()
 * @method static Builder<static>|GiftCard usable()
 *
 * @mixin \Eloquent
 */
class GiftCard extends Model
{
    use HasFactory;
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

    /**
     * @return HasMany<GiftCardTransaction, $this>
     */
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

    #[Scope]
    protected function usable(Builder $query): void
    {
        $query->where('is_active', true)
            ->where('current_balance', '>', 0)
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    protected function getStatusAttribute(): GiftCardStatus
    {
        if (! $this->is_active) {
            return GiftCardStatus::Inactive;
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return GiftCardStatus::Expired;
        }
        if ((float) $this->current_balance <= 0) {
            return GiftCardStatus::Depleted;
        }

        return GiftCardStatus::Active;
    }
}
