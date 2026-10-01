<?php

declare(strict_types=1);

namespace App\Exceptions\Orders;

use Illuminate\Contracts\Debug\ShouldntReport;
use RuntimeException;

class NoOrderableItemsException extends RuntimeException implements ShouldntReport
{
    /** What the customer sees when none of the cart's products can be ordered any more. */
    public const string CUSTOMER_MESSAGE = 'Some items in your cart are no longer available. Please review your cart.';

    public function __construct()
    {
        parent::__construct('None of the order items are available to order');
    }
}
