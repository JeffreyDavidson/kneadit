<?php

namespace App\Builders\Customers;

use App\Enums\Customers\CateringInquiryStatus;
use App\Models\Customers\CateringInquiry;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<CateringInquiry> */
class CateringInquiryQueryBuilder extends Builder
{
    public function quoted(): static
    {
        $this->where('status', CateringInquiryStatus::Quoted);

        return $this;
    }

    /** Inquiry or Quoted — open in the funnel, no decision yet. */
    public function openFunnel(): static
    {
        $this->whereIn('status', [
            CateringInquiryStatus::Inquiry,
            CateringInquiryStatus::Quoted,
        ]);

        return $this;
    }

    /** Active pipeline value — exclude completed and cancelled. */
    public function inPipeline(): static
    {
        $this->whereNotIn('status', [
            CateringInquiryStatus::Cancelled,
            CateringInquiryStatus::Completed,
        ]);

        return $this;
    }
}
