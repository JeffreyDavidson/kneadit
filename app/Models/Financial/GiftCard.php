<?php

namespace App\Models\Financial;

use App\Builders\Financial\GiftCardQueryBuilder;
use App\Casts\MoneyCentsCast;
use App\Casts\StripTagsCast;
use App\Enums\Financial\GiftCardStatus;
use App\Observers\LogsActivityObserver;
use Database\Factories\Financial\GiftCardFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property-read Collection<int, GiftCardTransaction> $transactions
 * @property-read int|null $transactions_count
 *
 * @method static GiftCardQueryBuilder|GiftCard newModelQuery()
 * @method static GiftCardQueryBuilder|GiftCard newQuery()
 * @method static GiftCardQueryBuilder|GiftCard query()
 * @method static GiftCardQueryBuilder|GiftCard usable()
 *
 * @property Carbon|null $expires_at
 * @property \App\ValueObjects\Money $initial_balance
 * @property \App\ValueObjects\Money $current_balance
 *
 * @mixin \Eloquent
 */
#[Fillable('code', 'initial_balance', 'current_balance', 'purchaser_name', 'purchaser_email', 'recipient_name', 'recipient_email', 'message', 'is_active', 'expires_at')]
#[ObservedBy(LogsActivityObserver::class)]
#[UseEloquentBuilder(GiftCardQueryBuilder::class)]
#[UseFactory(GiftCardFactory::class)]
class GiftCard extends Model
{
    /** @use HasFactory<GiftCardFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'initial_balance' => MoneyCentsCast::class,
            'current_balance' => MoneyCentsCast::class,
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
            get: fn () => GiftCardStatus::resolve($this) === GiftCardStatus::Active,
        );
    }
}
