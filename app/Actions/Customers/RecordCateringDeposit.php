<?php

namespace App\Actions\Customers;

use App\Enums\Customers\CateringInquiryStatus;
use App\Enums\Orders\PaymentStatus;
use App\Models\Customers\CateringInquiry;
use App\ValueObjects\Money;

/**
 * Stamps a deposit on a catering inquiry. If the inquiry was Quoted,
 * promotes it to Confirmed (the deposit is the booking commitment).
 * If a linked order exists and is still Unpaid, mark it Partial.
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

        if (($order = $inquiry->order()->first()) && $order->payment_status === PaymentStatus::Unpaid) {
            $order->update(['payment_status' => PaymentStatus::Partial]);
        }

        return $inquiry;
    }
}
