<?php

namespace App\Services\Tenants;

use App\Enums\Orders\DeliveryType;
use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentMethod;
use App\Enums\Orders\PaymentStatus;
use App\Services\Tenants\Contracts\LegacyOrderImporter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class DatabaseLegacyOrderImporter implements LegacyOrderImporter
{
    public function __construct(private readonly LegacyImportValueParser $parser) {}

    /**
     * @param array<int, array<string, mixed>> $orders
     * @param array<int, array<string, mixed>> $orderNotes
     * @param array<string, int> $customerIds
     * @param array<int, int> $couponIds
     * @return array<int, int>
     */
    public function import(array $orders, array $orderNotes, array $customerIds, array $couponIds): array
    {
        $ids = [];

        foreach ($orders as $order) {
            $email = Str::lower(trim($this->parser->string($order['customer_email'])));

            DB::table('orders')->updateOrInsert(
                ['order_number' => $order['order_number']],
                [
                    'customer_id' => $customerIds[$email],
                    'coupon_id' => isset($order['coupon_id']) ? ($couponIds[$this->parser->integer($order['coupon_id'])] ?? null) : null,
                    'status' => $this->enumValue(OrderStatus::class, $order['status'] ?? OrderStatus::Pending->value, 'order status'),
                    'payment_status' => $this->enumValue(PaymentStatus::class, $order['payment_status'] ?? PaymentStatus::Unpaid->value, 'payment status'),
                    'payment_method' => $this->enumValue(PaymentMethod::class, $order['payment_method'] ?? PaymentMethod::Other->value, 'payment method'),
                    'subtotal' => $this->parser->cents($order['subtotal'] ?? 0),
                    'delivery_fee' => $this->parser->cents($order['delivery_fee'] ?? 0),
                    'discount_amount' => $this->parser->cents($order['discount_amount'] ?? 0),
                    'tip_amount' => 0,
                    'gift_card_amount' => 0,
                    'total' => $this->parser->cents($order['total'] ?? 0),
                    'paypal_invoice_id' => $order['paypal_invoice_id'] ?? null,
                    'delivery_address' => $order['delivery_address'] ?? null,
                    'delivery_type' => $this->enumValue(DeliveryType::class, $order['fulfillment_type'] ?? DeliveryType::Pickup->value, 'fulfillment type'),
                    'delivery_date' => $order['requested_date'] ?? null,
                    'delivery_time' => $order['requested_time'] ?? null,
                    'notes' => $this->orderNotes($order, $orderNotes),
                    'created_at' => $order['created_at'] ?? now(),
                    'updated_at' => $order['updated_at'] ?? now(),
                ],
            );

            $ids[$this->parser->integer($order['id'])] = $this->parser->integer(
                DB::table('orders')->where('order_number', $order['order_number'])->value('id'),
            );
        }

        return $ids;
    }

    /**
     * @param array<string, mixed> $order
     * @param array<int, array<string, mixed>> $orderNotes
     */
    private function orderNotes(array $order, array $orderNotes): ?string
    {
        $notes = collect($orderNotes)
            ->filter(fn (array $note): bool => $this->parser->integer($note['order_id']) === $this->parser->integer($order['id']))
            ->sortBy([
                ['created_at', 'asc'],
                ['id', 'asc'],
            ])
            ->map(function (array $note): string {
                $timestamp = $this->parser->string($note['created_at'] ?? 'Unknown date');
                $type = Str::headline($this->parser->string($note['type'] ?? 'note'));

                return "[{$timestamp}] [{$type}] {$this->parser->string($note['content'])}";
            })
            ->values();

        $originalNotes = trim($this->parser->string($order['notes'] ?? ''));

        if ($notes->isEmpty()) {
            return $originalNotes !== '' ? $originalNotes : null;
        }

        $legacyHistory = "Legacy order history:\n" . $notes->implode("\n");

        return $originalNotes !== ''
            ? "{$originalNotes}\n\n{$legacyHistory}"
            : $legacyHistory;
    }

    /**
     * @param class-string<\BackedEnum> $enumClass
     */
    private function enumValue(string $enumClass, mixed $value, string $label): string
    {
        $normalized = Str::lower($this->parser->string($value));
        $enum = $enumClass::tryFrom($normalized);

        throw_if($enum === null, InvalidArgumentException::class, "Unsupported {$label} [{$normalized}].");

        return (string) $enum->value;
    }
}
