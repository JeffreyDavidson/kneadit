<?php

namespace App\Actions\Customers;

use App\Enums\Customers\CateringInquiryStatus;
use App\Events\Marketing\CateringQuoteRequested;
use App\Models\Customers\CateringInquiry;

class SendCateringQuote
{
    public function __construct(
        private readonly TransitionCateringInquiryStatus $transition,
    ) {}

    public function __invoke(CateringInquiry $inquiry): void
    {
        ($this->transition)($inquiry, CateringInquiryStatus::Quoted);

        event(new CateringQuoteRequested($inquiry));
    }
}
