<?php

namespace App\Reports\Customers;

use App\Enums\Customers\RfmSegment;
use App\Enums\Orders\PaymentStatus;
use App\Models\Customers\Customer;
use Illuminate\Database\Eloquent\Builder;

/**
 * Classifies customers into RFM segments (Recency, Frequency, Monetary).
 *
 * Thresholds are fixed v1 constants — tunable later via tenant settings
 * if bakers ask. The segmentation rules are:
 *   - Champions:   recency < 30d,  frequency >= 4, monetary >= 500
 *   - Loyal:       recency < 60d,  frequency >= 3, monetary >= 200
 *   - AtRisk:      60 <= recency < 180, frequency >= 3, monetary >= 200
 *   - New:         recency < 30d,  frequency <= 2
 *   - Hibernating: everything else (low engagement or 180+ days inactive)
 */
class RfmReport
{
    private const RECENT_DAYS = 30;

    private const ENGAGED_DAYS = 60;

    private const AT_RISK_DAYS = 180;

    private const FREQUENT_ORDERS = 4;

    private const LOYAL_ORDERS = 3;

    private const BIG_SPEND_DOLLARS = 500;

    private const LOYAL_SPEND_DOLLARS = 200;

    /**
     * Produce an RFM segmentation snapshot as of `now()`.
     *
     * @return array{
     *     total: int,
     *     segments: array<string, array{
     *         segment: RfmSegment,
     *         label: string,
     *         description: string,
     *         color: string,
     *         count: int,
     *         sampleCustomers: array<int, array{id: int, name: string, email: string, recency_days: int, frequency: int, monetary: float}>
     *     }>
     * }
     */
    public function generate(): array
    {
        $rows = Customer::query()
            ->whereHas('orders', fn (Builder $q) => $q->where('payment_status', PaymentStatus::Paid))
            ->withCount(['orders as frequency' => fn (Builder $q) => $q->where('payment_status', PaymentStatus::Paid)])
            ->withSum(['orders as monetary_cents' => fn (Builder $q) => $q->where('payment_status', PaymentStatus::Paid)], 'total')
            ->withMax(['orders as last_order_at' => fn (Builder $q) => $q->where('payment_status', PaymentStatus::Paid)], 'delivery_date')
            ->get();

        $now = now();

        /** @var array<string, int> $counts */
        $counts = [];
        /** @var array<string, array<int, array{id: int, name: string, email: string, recency_days: int, frequency: int, monetary: float}>> $samples */
        $samples = [];

        foreach (RfmSegment::cases() as $segment) {
            $counts[$segment->value] = 0;
            $samples[$segment->value] = [];
        }

        foreach ($rows as $customer) {
            $lastOrderAt = $customer->getAttribute('last_order_at');
            if ($lastOrderAt === null) {
                continue;
            }

            $recencyDays = (int) $now->copy()->diffInDays($lastOrderAt, true);
            $frequency = (int) $customer->getAttribute('frequency');
            // monetary_cents is a raw SUM() which bypasses the money cast
            // (see 2026_04_22_201500_convert_orders_money_columns_to_cents).
            $monetary = (float) ((int) ($customer->getAttribute('monetary_cents') ?? 0) / 100);

            $segment = $this->classify($recencyDays, $frequency, $monetary);
            $counts[$segment->value]++;

            if (count($samples[$segment->value]) < 5) {
                $samples[$segment->value][] = [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'recency_days' => $recencyDays,
                    'frequency' => $frequency,
                    'monetary' => $monetary,
                ];
            }
        }

        $segments = [];
        foreach (RfmSegment::cases() as $segment) {
            $segments[$segment->value] = [
                'segment' => $segment,
                'label' => $segment->getLabel(),
                'description' => $segment->description(),
                'color' => $segment->getColor(),
                'count' => $counts[$segment->value],
                'sampleCustomers' => $samples[$segment->value],
            ];
        }

        return [
            'total' => $rows->count(),
            'segments' => $segments,
        ];
    }

    private function classify(int $recencyDays, int $frequency, float $monetary): RfmSegment
    {
        if ($recencyDays < self::RECENT_DAYS && $frequency >= self::FREQUENT_ORDERS && $monetary >= self::BIG_SPEND_DOLLARS) {
            return RfmSegment::Champions;
        }

        if ($recencyDays < self::ENGAGED_DAYS && $frequency >= self::LOYAL_ORDERS && $monetary >= self::LOYAL_SPEND_DOLLARS) {
            return RfmSegment::Loyal;
        }

        if ($recencyDays >= self::ENGAGED_DAYS && $recencyDays < self::AT_RISK_DAYS && $frequency >= self::LOYAL_ORDERS && $monetary >= self::LOYAL_SPEND_DOLLARS) {
            return RfmSegment::AtRisk;
        }

        if ($recencyDays < self::RECENT_DAYS && $frequency <= 2) {
            return RfmSegment::New;
        }

        return RfmSegment::Hibernating;
    }
}
