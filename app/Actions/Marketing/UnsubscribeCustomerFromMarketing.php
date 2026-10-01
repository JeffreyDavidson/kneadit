<?php

declare(strict_types=1);

namespace App\Actions\Marketing;

use App\Models\Customers\Customer;

/**
 * Opts a customer out of marketing email. Idempotent: a customer who is
 * already opted out keeps their original opt-out time.
 */
class UnsubscribeCustomerFromMarketing
{
    public function __invoke(Customer $customer): void
    {
        if ($customer->marketing_opted_out_at !== null) {
            return;
        }

        $customer->update(['marketing_opted_out_at' => now()]);
    }
}
