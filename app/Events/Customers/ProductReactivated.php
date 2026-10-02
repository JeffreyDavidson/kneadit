<?php

declare(strict_types=1);

namespace App\Events\Customers;

use App\Models\Inventory\Product;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class ProductReactivated implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly Product $product,
    ) {}
}
