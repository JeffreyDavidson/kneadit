<?php

declare(strict_types=1);

namespace App\Builders\Financial;

use App\Models\Financial\GiftCard;
use App\Services\Scheduling\BakeryClock;
use Illuminate\Database\Eloquent\Builder;

/**
 * Expiry is a bakery-local date and inclusive: a card expiring on a date works
 * through the end of that day in the bakery's timezone.
 *
 * @template TModel of GiftCard
 *
 * @extends Builder<TModel>
 */
class GiftCardQueryBuilder extends Builder
{
    public function usable(): static
    {
        $this->where('is_active', true)
            ->where('current_balance', '>', 0)
            ->where(function (Builder $q): void {
                $q->whereNull('expires_at')->orWhereDate('expires_at', '>=', $this->bakeryToday());
            });

        return $this;
    }

    public function expired(): static
    {
        $this->whereNotNull('expires_at')->whereDate('expires_at', '<', $this->bakeryToday());

        return $this;
    }

    private function bakeryToday(): string
    {
        return resolve(BakeryClock::class)->today()->toDateString();
    }
}
