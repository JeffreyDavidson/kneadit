<?php

namespace App\Builders\Financial;

use App\Models\Financial\Coupon;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<Coupon> */
class CouponQueryBuilder extends Builder
{
    public function active(): static
    {
        $this->where('is_active', true);

        return $this;
    }

    public function valid(): static
    {
        $this->active()
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->where(fn (Builder $q) => $q->whereNull('max_uses')->orWhereColumn('used_count', '<', 'max_uses'));

        return $this;
    }
}
