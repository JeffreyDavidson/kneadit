<?php

namespace App\Actions\Marketing;

use App\Mail\Customers\BulkCustomerMessageMail;
use App\Models\Customers\Customer;
use Illuminate\Support\Facades\Mail;

/**
 * Queues a one-off message to a hand-picked set of customers.
 * Distinct from SendCustomerCampaign — no campaign record, no
 * recipient log, no open tracking. Use for ad-hoc operational messages
 * (pickup window changes, "we have a question about your order", etc.).
 *
 * Returns the number of recipients actually queued (skips customers
 * with no email).
 */
class SendBulkCustomerMessage
{
    /**
     * @param iterable<Customer> $customers
     */
    public function __invoke(iterable $customers, string $messageSubject, string $body): int
    {
        $sent = 0;

        foreach ($customers as $customer) {
            if (! $customer->email) {
                continue;
            }

            Mail::to($customer->email)->queue(
                new BulkCustomerMessageMail($customer, $messageSubject, $body),
            );

            $sent++;
        }

        return $sent;
    }
}
