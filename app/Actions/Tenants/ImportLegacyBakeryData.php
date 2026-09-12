<?php

namespace App\Actions\Tenants;

use App\Enums\Orders\DeliveryType;
use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentMethod;
use App\Enums\Orders\PaymentStatus;
use App\Services\Tenants\Contracts\LegacyCatalogImporter;
use App\Services\Tenants\Contracts\LegacyCouponImporter;
use App\Services\Tenants\Contracts\LegacyCustomerImporter;
use App\Services\Tenants\Contracts\LegacyEngagementImporter;
use App\Services\Tenants\Contracts\LegacyFinancialImporter;
use App\Services\Tenants\Contracts\LegacyOrderItemImporter;
use App\Services\Tenants\Contracts\LegacyRecipeImporter;
use App\Services\Tenants\Contracts\LegacyReviewImporter;
use App\Services\Tenants\Contracts\LegacySchedulingImporter;
use App\Services\Tenants\Contracts\LegacySettingsImporter;
use App\Services\Tenants\LegacyImportValueParser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ImportLegacyBakeryData
{
    public function __construct(
        private readonly LegacyBakeryDataValidator $validator,
        private readonly LegacyCatalogImporter $catalogImporter,
        private readonly LegacyCouponImporter $couponImporter,
        private readonly LegacyCustomerImporter $customerImporter,
        private readonly LegacyFinancialImporter $financialImporter,
        private readonly LegacyEngagementImporter $engagementImporter,
        private readonly LegacyOrderItemImporter $orderItemImporter,
        private readonly LegacyReviewImporter $reviewImporter,
        private readonly LegacyRecipeImporter $recipeImporter,
        private readonly LegacySchedulingImporter $schedulingImporter,
        private readonly LegacySettingsImporter $settingsImporter,
        private readonly LegacyImportValueParser $valueParser,
    ) {}

    /**
     * @param array<string, array<int, array<string, mixed>>> $data
     * @return array<string, int>
     */
    public function __invoke(array $data): array
    {
        $this->validator->validate($data);

        return DB::transaction(function () use ($data): array {
            $catalogIds = $this->catalogImporter->import(
                $data['categories'] ?? [],
                $data['products'] ?? [],
            );
            $categoryIds = $catalogIds['category_ids'];
            $productIds = $catalogIds['product_ids'];
            $couponIds = $this->couponImporter->import($data['coupons'] ?? []);
            $customerIds = $this->customerImporter->import($data['orders'] ?? []);
            $orderIds = $this->importOrders(
                $data['orders'] ?? [],
                $data['order_notes'] ?? [],
                $customerIds,
                $couponIds,
            );

            $this->orderItemImporter->import($data['order_items'] ?? [], $orderIds, $productIds);
            $this->reviewImporter->import($data['reviews'] ?? [], $productIds, $orderIds);
            $this->recipeImporter->import($data['recipes'] ?? [], $data['recipe_ingredients'] ?? [], $data['recipe_stages'] ?? [], $productIds);
            $this->financialImporter->import($data['expenses'] ?? [], $data['incomes'] ?? []);
            $this->schedulingImporter->import($data['capacity_limits'] ?? [], $data['holidays'] ?? []);
            $this->engagementImporter->import($data['contact_messages'] ?? [], $data['waitlist_entries'] ?? [], $data['customer_favorites'] ?? [], $productIds);
            $this->settingsImporter->import($data['settings'] ?? []);

            return [
                'categories' => count($categoryIds),
                'products' => count($productIds),
                'coupons' => count($couponIds),
                'customers' => count($customerIds),
                'orders' => count($orderIds),
                'order_notes' => count($data['order_notes'] ?? []),
                'order_items' => count($data['order_items'] ?? []),
                'reviews' => count($data['reviews'] ?? []),
                'recipes' => count($data['recipes'] ?? []),
                'expenses' => count($data['expenses'] ?? []),
                'incomes' => count($data['incomes'] ?? []),
                'capacity_limits' => count($data['capacity_limits'] ?? []),
                'holidays' => count($data['holidays'] ?? []),
                'contact_messages' => count($data['contact_messages'] ?? []),
                'waitlist_entries' => count($data['waitlist_entries'] ?? []),
                'customer_favorites' => count($data['customer_favorites'] ?? []),
                'settings' => count($data['settings'] ?? []),
            ];
        });
    }

    /** @param array<int, array<string, mixed>> $orders
     * @param array<int, array<string, mixed>> $orderNotes
     * @param array<string, int> $customerIds
     * @param array<int, int> $couponIds
     * @return array<int, int>
     */
    private function importOrders(array $orders, array $orderNotes, array $customerIds, array $couponIds): array
    {
        $ids = [];

        foreach ($orders as $order) {
            $email = Str::lower(trim($this->stringValue($order['customer_email'])));
            DB::table('orders')->updateOrInsert(
                ['order_number' => $order['order_number']],
                [
                    'customer_id' => $customerIds[$email],
                    'coupon_id' => isset($order['coupon_id']) ? ($couponIds[$this->parseLegacyInteger($order['coupon_id'])] ?? null) : null,
                    'status' => $this->orderStatus($order['status'] ?? OrderStatus::Pending->value),
                    'payment_status' => $this->paymentStatus($order['payment_status'] ?? PaymentStatus::Unpaid->value),
                    'payment_method' => $this->paymentMethod($order['payment_method'] ?? PaymentMethod::Other->value),
                    'subtotal' => $this->cents($order['subtotal'] ?? 0),
                    'delivery_fee' => $this->cents($order['delivery_fee'] ?? 0),
                    'discount_amount' => $this->cents($order['discount_amount'] ?? 0),
                    'tip_amount' => 0,
                    'gift_card_amount' => 0,
                    'total' => $this->cents($order['total'] ?? 0),
                    'paypal_invoice_id' => $order['paypal_invoice_id'] ?? null,
                    'delivery_address' => $order['delivery_address'] ?? null,
                    'delivery_type' => $this->deliveryType($order['fulfillment_type'] ?? DeliveryType::Pickup->value),
                    'delivery_date' => $order['requested_date'] ?? null,
                    'delivery_time' => $order['requested_time'] ?? null,
                    'notes' => $this->orderNotes($order, $orderNotes),
                    'created_at' => $order['created_at'] ?? now(),
                    'updated_at' => $order['updated_at'] ?? now(),
                ],
            );
            $ids[$this->parseLegacyInteger($order['id'])] = $this->parseLegacyInteger(DB::table('orders')->where('order_number', $order['order_number'])->value('id'));
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
            ->filter(fn (array $note): bool => $this->parseLegacyInteger($note['order_id']) === $this->parseLegacyInteger($order['id']))
            ->sortBy([
                ['created_at', 'asc'],
                ['id', 'asc'],
            ])
            ->map(function (array $note): string {
                $timestamp = $this->stringValue($note['created_at'] ?? 'Unknown date');
                $type = Str::headline($this->stringValue($note['type'] ?? 'note'));

                return "[{$timestamp}] [{$type}] {$this->stringValue($note['content'])}";
            })
            ->values();

        $originalNotes = trim($this->stringValue($order['notes'] ?? ''));

        if ($notes->isEmpty()) {
            return $originalNotes !== '' ? $originalNotes : null;
        }

        $legacyHistory = "Legacy order history:\n" . $notes->implode("\n");

        return $originalNotes !== ''
            ? "{$originalNotes}\n\n{$legacyHistory}"
            : $legacyHistory;
    }

    private function orderStatus(mixed $value): string
    {
        $normalized = Str::lower($this->stringValue($value));

        $status = OrderStatus::tryFrom($normalized);
        throw_if($status === null, InvalidArgumentException::class, "Unsupported order status [{$normalized}].");

        return $status->value;
    }

    private function paymentStatus(mixed $value): string
    {
        $normalized = Str::lower($this->stringValue($value));

        $status = PaymentStatus::tryFrom($normalized);
        throw_if($status === null, InvalidArgumentException::class, "Unsupported payment status [{$normalized}].");

        return $status->value;
    }

    private function paymentMethod(mixed $value): string
    {
        $normalized = Str::lower($this->stringValue($value));

        $method = PaymentMethod::tryFrom($normalized);
        throw_if($method === null, InvalidArgumentException::class, "Unsupported payment method [{$normalized}].");

        return $method->value;
    }

    private function deliveryType(mixed $value): string
    {
        $normalized = Str::lower($this->stringValue($value));

        $type = DeliveryType::tryFrom($normalized);
        throw_if($type === null, InvalidArgumentException::class, "Unsupported fulfillment type [{$normalized}].");

        return $type->value;
    }

    private function cents(mixed $dollars): int
    {
        return $this->valueParser->cents($dollars);
    }

    private function stringValue(mixed $value): string
    {
        return $this->valueParser->string($value);
    }

    private function parseLegacyInteger(mixed $value): int
    {
        return $this->valueParser->integer($value);
    }
}
