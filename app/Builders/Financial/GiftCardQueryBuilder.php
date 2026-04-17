<?php

namespace App\Builders\Financial;

use App\Models\Financial\GiftCard;
use Illuminate\Database\Eloquent\Builder;

/**
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
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));

        return $this;
    }
}
