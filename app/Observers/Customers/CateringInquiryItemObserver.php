<?php

namespace App\Observers\Customers;

use App\Models\Customers\CateringInquiry;
use App\Models\Customers\CateringInquiryItem;
use App\ValueObjects\Money;

class CateringInquiryItemObserver
{
    public function created(CateringInquiryItem $item): void
    {
        $this->recomputeQuoteTotal($item);
    }

    public function updated(CateringInquiryItem $item): void
    {
        $this->recomputeQuoteTotal($item);
    }

    public function deleted(CateringInquiryItem $item): void
    {
        $this->recomputeQuoteTotal($item);
    }

    /**
     * Sum cents in PHP rather than via raw aggregation — `unit_price` is a
     * bigint cents column with a Money cast; `->sum('unit_price')` would
     * return cents and bypass the cast, but here we want quantity-weighted
     * totals which the DB layer can't compute portably without selectRaw.
     *
     * Use a fresh query for the parent rather than $item->inquiry: strict-mode
     * lazy-loading throws on the unloaded BelongsTo, and we don't always have
     * the parent eager-loaded at observer time.
     */
    private function recomputeQuoteTotal(CateringInquiryItem $item): void
    {
        $inquiry = CateringInquiry::query()->find($item->catering_inquiry_id);

        if ($inquiry === null) {
            return;
        }

        $sumCents = $inquiry->items()->get()
            ->sum(fn (CateringInquiryItem $i): int => $i->unit_price->cents() * $i->quantity);

        $inquiry->update(['quoted_amount' => Money::fromCents((int) $sumCents)]);
    }
}
