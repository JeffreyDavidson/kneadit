<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Customers;

use App\Enums\Marketing\BulkMessagePurpose;

final readonly class BulkMessageOutcome
{
    public function __construct(
        public int $sent,
        public int $skippedNoEmail,
        public int $skippedIneligible,
    ) {}

    /** One-line summary for the baker, e.g. "3 sent; 2 skipped (no open order)". */
    public function summary(BulkMessagePurpose $purpose): string
    {
        return collect([
            "{$this->sent} sent",
            $this->skippedIneligible > 0 ? "{$this->skippedIneligible} skipped ({$purpose->skipReason()})" : null,
            $this->skippedNoEmail > 0 ? "{$this->skippedNoEmail} skipped (no email address)" : null,
        ])->filter()->implode('; ');
    }
}
