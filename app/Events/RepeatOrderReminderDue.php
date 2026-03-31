<?php

namespace App\Events;

use App\Models\Customer;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class RepeatOrderReminderDue implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly Customer $customer,
        public readonly int $daysSinceLastOrder,
    ) {}
}
