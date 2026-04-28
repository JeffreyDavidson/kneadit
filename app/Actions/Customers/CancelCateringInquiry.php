<?php

namespace App\Actions\Customers;

use App\Actions\Orders\TransitionOrderStatus;
use App\Enums\Customers\CateringInquiryStatus;
use App\Enums\Orders\OrderStatus;
use App\Models\Customers\CateringInquiry;

class CancelCateringInquiry
{
    public function __construct(
        private readonly TransitionCateringInquiryStatus $transitionInquiry,
        private readonly TransitionOrderStatus $transitionOrder,
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

        if ($order = $inquiry->order()->first()) {
            $allowed = TransitionOrderStatus::allowedTransitions($order);

            if (in_array(OrderStatus::Cancelled, $allowed, true)) {
                ($this->transitionOrder)($order, OrderStatus::Cancelled);
            }
        }

        ($this->transitionInquiry)($inquiry, CateringInquiryStatus::Cancelled);
    }
}
