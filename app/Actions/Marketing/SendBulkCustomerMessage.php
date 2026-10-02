<?php

namespace App\Actions\Marketing;

use App\DataTransferObjects\Customers\BulkMessageOutcome;
use App\Enums\Marketing\BulkMessagePurpose;
use App\Mail\Customers\BulkCustomerMessageMail;
use App\Mail\Customers\OrderUpdateMessageMail;
use App\Models\Customers\Customer;
use Illuminate\Support\Facades\Mail;

/**
 * Queues a one-off message to a hand-picked set of customers.
 * Distinct from SendCustomerCampaign — no campaign record, no
 * recipient log, no open tracking.
 *
 * The purpose decides who gets it. An order update is transactional: it goes
 * to selected customers with an open order, even if they unsubscribed from
 * marketing. A promotion is marketing: it skips unsubscribed customers and
 * carries the unsubscribe link and headers.
 *
 * Customers with no email address are always skipped.
 */
class SendBulkCustomerMessage
{
    /**
     * @param  iterable<int, Customer>  $customers
     */
    public function __invoke(
        iterable $customers,
        BulkMessagePurpose $purpose,
        string $messageSubject,
        string $body,
    ): BulkMessageOutcome {
        $customers = collect($customers);
        $withOpenOrder = $purpose === BulkMessagePurpose::OrderUpdate
            ? Customer::query()->whereKey($customers->pluck('id'))->withOpenOrder()->pluck('id')
            : collect();

        $sent = 0;
        $skippedNoEmail = 0;
        $skippedIneligible = 0;

        foreach ($customers as $customer) {
            if (! $customer->email) {
                $skippedNoEmail++;

                continue;
            }

            $eligible = match ($purpose) {
                BulkMessagePurpose::OrderUpdate => $withOpenOrder->contains($customer->id),
                BulkMessagePurpose::Promotion => $customer->marketing_opted_out_at === null,
            };

            if (! $eligible) {
                $skippedIneligible++;

                continue;
            }

            Mail::to($customer->email)->queue(match ($purpose) {
                BulkMessagePurpose::OrderUpdate => new OrderUpdateMessageMail($customer, $messageSubject, $body),
                BulkMessagePurpose::Promotion => new BulkCustomerMessageMail($customer, $messageSubject, $body),
            });

            $sent++;
        }

        return new BulkMessageOutcome($sent, $skippedNoEmail, $skippedIneligible);
    }
}
