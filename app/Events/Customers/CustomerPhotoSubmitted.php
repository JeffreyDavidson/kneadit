<?php

namespace App\Events\Customers;

use App\Models\Customers\CustomerPhoto;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class CustomerPhotoSubmitted implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly CustomerPhoto $photo,
    ) {}
}
