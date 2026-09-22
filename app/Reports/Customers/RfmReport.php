<?php

namespace App\Reports\Customers;

use App\DataTransferObjects\Customers\RfmCustomerSample;
use App\DataTransferObjects\Customers\RfmReportResult;
use App\DataTransferObjects\Customers\RfmSegmentResult;
use App\Enums\Customers\RfmSegment;
use App\Models\Customers\Customer;
use App\Services\Customers\RfmClassifier;
use App\ValueObjects\Money;
use DateTimeInterface;
use Illuminate\Support\Arr;

/**
 * Classifies customers into RFM segments (Recency, Frequency, Monetary).
 *
 * Thresholds + classification rules live on RfmClassifier so this report
 * and the customer-campaign recipient resolver share a single source of
 * truth. See app/Services/Customers/RfmClassifier.php.
 */
class RfmReport
{
    public function __construct(
        private readonly RfmClassifier $classifier,
    ) {}

    /** Produce an RFM segmentation snapshot as of `now()`. */
    public function generate(): RfmReportResult
    {
        $rows = Customer::query()
            ->withRfmMetrics()
            ->get();

        $now = now();

        /** @var array<string, int> $counts */
        $counts = [];
        /** @var array<string, list<RfmCustomerSample>> $samples */
        $samples = [];

        foreach (RfmSegment::cases() as $segment) {
            $counts[$segment->value] = 0;
            $samples[$segment->value] = [];
        }

        foreach ($rows as $customer) {
            $lastOrderAt = $customer->getAttribute('last_order_at');
            if (! is_string($lastOrderAt) && ! $lastOrderAt instanceof DateTimeInterface) {
                continue;
            }

            $recencyDays = (int) $now->copy()->diffInDays($lastOrderAt, true);
            $frequency = Arr::integer($customer->getAttributes(), 'frequency', 0);
            // monetary_cents is a raw SUM() which bypasses the money cast
            // (see 2026_04_22_201500_convert_orders_money_columns_to_cents).
            $monetary = Money::fromCents(Arr::integer($customer->getAttributes(), 'monetary_cents', 0));

            $segment = $this->classifier->classify($recencyDays, $frequency, $monetary->dollars());
            $counts[$segment->value]++;

            if (count($samples[$segment->value]) < 5) {
                $samples[$segment->value][] = new RfmCustomerSample(
                    id: $customer->id,
                    name: $customer->name,
                    email: $customer->email,
                    recencyDays: $recencyDays,
                    frequency: $frequency,
                    monetary: $monetary,
                );
            }
        }

        /** @var array<string, RfmSegmentResult> $segments */
        $segments = [];
        foreach (RfmSegment::cases() as $segment) {
            $segments[$segment->value] = new RfmSegmentResult(
                segment: $segment,
                label: $segment->getLabel(),
                description: $segment->description(),
                color: $segment->getColor(),
                count: $counts[$segment->value],
                sampleCustomers: $samples[$segment->value],
            );
        }

        return new RfmReportResult(
            total: $rows->count(),
            segments: $segments,
        );
    }
}
