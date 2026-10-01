<?php

declare(strict_types=1);

namespace App\Actions\Marketing;

use App\Models\Customers\Customer;

/**
 * Lets a customer opt back in to marketing email. Only the customer can do this
 * (from their signed link); staff have no way to re-subscribe someone.
 */
class ResubscribeCustomerToMarketing
{
    public function __invoke(Customer $customer): void
    {
        $customer->update(['marketing_opted_out_at' => null]);
    }
}
