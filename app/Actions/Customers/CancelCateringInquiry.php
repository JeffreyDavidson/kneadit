<?php

namespace App\Actions\Customers;

use App\Enums\Customers\CateringInquiryStatus;
use App\Models\Customers\CateringInquiry;

class CancelCateringInquiry
{
    public function __construct(
        private readonly TransitionCateringInquiryStatus $transition,
    ) {}

    public function __invoke(CateringInquiry $inquiry, ?string $reason = null): void
    {
        $trimmed = $reason !== null ? trim($reason) : '';

        if ($trimmed !== '') {
            $stamped = '[' . now()->toDateString() . '] Cancelled: ' . $trimmed;

            $inquiry->notes = $inquiry->notes
                ? $stamped . "\n\n" . $inquiry->notes
                : $stamped;

            $inquiry->save();
        }

        ($this->transition)($inquiry, CateringInquiryStatus::Cancelled);
    }
}
