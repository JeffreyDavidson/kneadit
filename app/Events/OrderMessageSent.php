<?php

namespace App\Events;

use App\Models\OrderMessage;
use Illuminate\Foundation\Events\Dispatchable;

class OrderMessageSent
{
    use Dispatchable;

    public function __construct(
        public readonly OrderMessage $message,
    ) {}
}
