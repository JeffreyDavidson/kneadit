<?php

namespace App\Services\Customers;

use App\Enums\Customers\RfmSegment;
use App\Models\Customers\Customer;
use DateTimeInterface;
use Illuminate\Support\Collection;

/**
 * Resolves the recipient list for a customer campaign based on the
 * configured target segment.
 *
 * - 'all' → every customer with at least one paid order (consistent with
 *   the active-customer definition used by RfmReport).
 * - one of the RfmSegment values → only customers that fall into that
 *   segment per RfmClassifier.
 *
 * Returns a Collection of Customer models (with email + name); the caller
 * is responsible for queuing the actual mail.
 */
class ResolveCampaignRecipients
{
    public function __construct(
        private RfmClassifier $classifier,
    ) {}

    /** @return Collection<int, Customer> */
    public function __invoke(string $targetSegment): Collection
    {
        $rows = Customer::query()
            ->withRfmMetrics()
            ->whereNotNull('email')
            ->get();

        if ($targetSegment === 'all') {
            return $rows->values();
        }

        $segment = RfmSegment::tryFrom($targetSegment);
        if ($segment === null) {
            return (new Customer)->newCollection();
        }

        $now = now();

        return $rows->filter(function (Customer $customer) use ($segment, $now): bool {
            $lastOrderAt = $customer->getAttribute('last_order_at');
            if (! is_string($lastOrderAt) && ! $lastOrderAt instanceof DateTimeInterface) {
                return false;
            }

            $recencyDays = (int) $now->copy()->diffInDays($lastOrderAt, true);
            $frequency = filter_var($customer->getAttribute('frequency'), FILTER_VALIDATE_INT);
            $monetaryCents = filter_var($customer->getAttribute('monetary_cents'), FILTER_VALIDATE_INT);

            if ($frequency === false || $monetaryCents === false) {
                return false;
            }

            $monetary = $monetaryCents / 100;

            return $this->classifier->classify($recencyDays, $frequency, $monetary) === $segment;
        })->values();
    }
}
