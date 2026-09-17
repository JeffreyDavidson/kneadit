<?php

declare(strict_types=1);

namespace App\Events\Marketing;

use App\Models\Customers\CateringInquiry;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class CateringInquiryReceived implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly CateringInquiry $inquiry,
    ) {}
}
