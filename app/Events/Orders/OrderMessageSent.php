<?php

namespace App\Events\Orders;

use App\Models\OrderMessage;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class OrderMessageSent implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly OrderMessage $message,
    ) {}
}
