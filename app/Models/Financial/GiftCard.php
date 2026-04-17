<?php

namespace App\Models\Financial;

use App\Builders\Financial\GiftCardQueryBuilder;
use App\Casts\StripTagsCast;
use App\Enums\Financial\GiftCardStatus;
use App\Observers\LogsActivityObserver;
use Database\Factories\Financial\GiftCardFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property-read GiftCardStatus $status
 * @property-read Collection<int, GiftCardTransaction> $transactions
 * @property-read int|null $transactions_count
 *
 * @method static \Database\Factories\GiftCardFactory factory($count = null, $state = [])
 * @method static GiftCardQueryBuilder|GiftCard newModelQuery()
 * @method static GiftCardQueryBuilder|GiftCard newQuery()
 * @method static GiftCardQueryBuilder|GiftCard query()
 * @method static GiftCardQueryBuilder|GiftCard usable()
 *
 * @property Carbon|null $expires_at
 *
 * @mixin \Eloquent
 */
#[Fillable('code', 'initial_balance', 'current_balance', 'purchaser_name', 'purchaser_email', 'recipient_name', 'recipient_email', 'message', 'is_active', 'expires_at')]
#[ObservedBy(LogsActivityObserver::class)]
#[UseEloquentBuilder(GiftCardQueryBuilder::class)]
class GiftCard extends Model
{
    /** @use HasFactory<GiftCardFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'initial_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'is_active' => 'boolean',
            'expires_at' => 'date',
            'message' => StripTagsCast::class,
        ];
    }

    /**
     * @return HasMany<GiftCardTransaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(GiftCardTransaction::class);
    }

    /** @return Attribute<bool, never> */
    protected function isUsable(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === GiftCardStatus::Active,
        );
    }

    /** @return Attribute<GiftCardStatus, never> */
    protected function status(): Attribute
    {
        return Attribute::make(
            get: function () {
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
            },
        );
    }

    protected static function newFactory(): GiftCardFactory
    {
        return GiftCardFactory::new();
    }
}
