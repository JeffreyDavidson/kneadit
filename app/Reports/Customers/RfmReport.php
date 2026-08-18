<?php

namespace App\Reports\Customers;

use App\Enums\Customers\RfmSegment;
use App\Enums\Orders\PaymentStatus;
use App\Models\Customers\Customer;
use App\Services\Customers\RfmClassifier;
use Illuminate\Database\Eloquent\Builder;

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
        private RfmClassifier $classifier,
    ) {}

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

            $segment = $this->classifier->classify($recencyDays, $frequency, $monetary);
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
}
