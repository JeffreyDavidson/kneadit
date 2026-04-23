<?php

namespace App\Actions\Customers;

use App\Enums\Customers\CateringInquiryStatus;
use App\Models\Customers\CateringInquiry;
use App\ValueObjects\Money;

/**
 * Stamps a deposit on a catering inquiry. If the inquiry was Quoted,
 * promotes it to Confirmed (the deposit is the booking commitment).
 */
class RecordCateringDeposit
{
    public function __invoke(CateringInquiry $inquiry, float $amountDollars, ?string $reference = null): CateringInquiry
    {
        $inquiry->forceFill([
            'deposit_amount' => Money::fromDollars(max(0.0, $amountDollars)),
            'deposit_paid_at' => now(),
            'deposit_reference' => $reference !== null && trim($reference) !== '' ? trim($reference) : null,
        ]);

        if ($inquiry->status === CateringInquiryStatus::Quoted) {
            $inquiry->status = CateringInquiryStatus::Confirmed;
        }

        $inquiry->save();

        return $inquiry;
    }

    /**
     * Suggested deposit amount from the quoted total + setting percent.
     */
    public function suggestedAmount(CateringInquiry $inquiry, int $depositPercent): float
    {
        if (! $inquiry->quoted_amount || $depositPercent <= 0) {
            return 0.0;
        }

        return round($inquiry->quoted_amount->dollars() * (min(100, $depositPercent) / 100), 2);
    }
}
