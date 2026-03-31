<?php

namespace App\Events;

use App\Models\CateringInquiry;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class CateringQuoteRequested implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly CateringInquiry $inquiry,
    ) {}
}
