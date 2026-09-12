<?php

namespace App\Actions\Tenants;

use App\Contracts\Tenants\LegacyCatalogImporter;
use App\Contracts\Tenants\LegacyCouponImporter;
use App\Contracts\Tenants\LegacyCustomerImporter;
use App\Contracts\Tenants\LegacyFinancialImporter;
use App\Contracts\Tenants\LegacyOrderItemImporter;
use App\Contracts\Tenants\LegacyReviewImporter;
use App\Contracts\Tenants\LegacySettingsImporter;
use App\Enums\Orders\DeliveryType;
use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentMethod;
use App\Enums\Orders\PaymentStatus;
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
        private readonly LegacyOrderItemImporter $orderItemImporter,
        private readonly LegacyReviewImporter $reviewImporter,
        private readonly LegacySettingsImporter $settingsImporter,
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
            $this->importRecipes($data['recipes'] ?? [], $data['recipe_ingredients'] ?? [], $data['recipe_stages'] ?? [], $productIds);
            $this->financialImporter->import($data['expenses'] ?? [], $data['incomes'] ?? []);
            $this->importCapacityLimits($data['capacity_limits'] ?? []);
            $this->importHolidays($data['holidays'] ?? []);
            $this->importEngagement($data['contact_messages'] ?? [], $data['waitlist_entries'] ?? [], $data['customer_favorites'] ?? [], $productIds);
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

    /**
     * @param array<int, array<string, mixed>> $recipes
     * @param array<int, array<string, mixed>> $ingredients
     * @param array<int, array<string, mixed>> $stages
     * @param array<int, int> $productIds
     */
    private function importRecipes(array $recipes, array $ingredients, array $stages, array $productIds): void
    {
        foreach ($recipes as $recipe) {
            $recipeIngredients = array_values(array_map(
                fn (array $ingredient): array => [
                    'name' => $ingredient['name'],
                    'quantity' => $this->floatValue($ingredient['quantity']),
                    'unit' => $ingredient['unit'],
                    'cost' => $this->floatValue($ingredient['cost_per_unit'] ?? 0),
                ],
                array_filter($ingredients, fn (array $ingredient): bool => $this->parseLegacyInteger($ingredient['recipe_id']) === $this->parseLegacyInteger($recipe['id'])),
            ));
            $recipeStages = array_values(array_filter($stages, fn (array $stage): bool => $this->parseLegacyInteger($stage['recipe_id']) === $this->parseLegacyInteger($recipe['id'])));
            usort($recipeStages, fn (array $first, array $second): int => ($first['sort_order'] ?? 0) <=> ($second['sort_order'] ?? 0));
            $instructions = collect($recipeStages)
                ->map(fn (array $stage): string => $this->stringValue($stage['name']) . "\n" . $this->stringValue($stage['instructions']))
                ->implode("\n\n");
            $cost = collect($recipeIngredients)->sum(fn (array $ingredient): float => $ingredient['quantity'] * $ingredient['cost']);

            DB::table('recipes')->updateOrInsert(
                ['name' => $recipe['name']],
                [
                    'product_id' => isset($recipe['product_id']) ? ($productIds[$this->parseLegacyInteger($recipe['product_id'])] ?? null) : null,
                    'ingredients' => json_encode($recipeIngredients, JSON_THROW_ON_ERROR),
                    'instructions' => $instructions ?: ($recipe['description'] ?? ''),
                    'prep_time_minutes' => $recipe['prep_time_minutes'] ?? 0,
                    'cost' => $this->cents($cost),
                    'created_at' => $recipe['created_at'] ?? now(),
                    'updated_at' => $recipe['updated_at'] ?? now(),
                ],
            );
        }
    }

    /** @param array<int, array<string, mixed>> $capacityLimits */
    private function importCapacityLimits(array $capacityLimits): void
    {
        foreach ($capacityLimits as $capacityLimit) {
            $dayOfWeek = $capacityLimit['day_of_week'] ?? null;
            $specificDate = $capacityLimit['specific_date'] ?? null;
            $date = $specificDate ?? now()->startOfWeek()->addDays($this->parseLegacyInteger($dayOfWeek))->toDateString();

            DB::table('capacity_limits')->updateOrInsert(
                $specificDate ? ['specific_date' => $specificDate] : ['day_of_week' => $this->stringValue($dayOfWeek)],
                [
                    'date' => $date,
                    'max_orders' => $capacityLimit['max_orders'],
                    'is_blocked' => $capacityLimit['is_blocked'] ?? false,
                    'notes' => $capacityLimit['notes'] ?? null,
                    'created_at' => $capacityLimit['created_at'] ?? now(),
                    'updated_at' => $capacityLimit['updated_at'] ?? now(),
                ],
            );
        }
    }

    /** @param array<int, array<string, mixed>> $holidays */
    private function importHolidays(array $holidays): void
    {
        foreach ($holidays as $holiday) {
            DB::table('holidays')->updateOrInsert(
                ['name' => $holiday['name'], 'date' => $holiday['date']],
                [
                    'lead_days' => $holiday['lead_days'] ?? 7,
                    'order_deadline' => $holiday['order_deadline'] ?? null,
                    'prep_start' => $holiday['prep_start'] ?? null,
                    'max_orders' => $holiday['max_orders'] ?? null,
                    'notes' => $holiday['notes'] ?? null,
                    'is_active' => $holiday['is_active'] ?? true,
                    'created_at' => $holiday['created_at'] ?? now(),
                    'updated_at' => $holiday['updated_at'] ?? now(),
                ],
            );
        }
    }

    /**
     * @param array<int, array<string, mixed>> $contactMessages
     * @param array<int, array<string, mixed>> $waitlistEntries
     * @param array<int, array<string, mixed>> $favorites
     * @param array<int, int> $productIds
     */
    private function importEngagement(array $contactMessages, array $waitlistEntries, array $favorites, array $productIds): void
    {
        foreach ($contactMessages as $message) {
            DB::table('contact_messages')->updateOrInsert(
                ['email' => $message['email'], 'message' => $message['message']],
                [
                    'name' => $message['name'],
                    'subject' => $message['subject'] ?? 'Legacy contact message',
                    'is_read' => ($message['status'] ?? 'new') !== 'new',
                    'created_at' => $message['created_at'] ?? now(),
                    'updated_at' => $message['updated_at'] ?? now(),
                ],
            );
        }

        foreach ($waitlistEntries as $entry) {
            $notes = collect([$entry['product_interest'] ?? null, $entry['notes'] ?? null])->filter()->implode("\n\n");
            DB::table('waitlist_entries')->updateOrInsert(
                ['customer_email' => $entry['customer_email'], 'requested_date' => $entry['requested_date']],
                [
                    'customer_name' => $entry['customer_name'],
                    'customer_phone' => $entry['customer_phone'] ?? null,
                    'product_id' => isset($entry['product_id']) ? ($productIds[$this->parseLegacyInteger($entry['product_id'])] ?? null) : null,
                    'notes' => $notes ?: null,
                    'status' => $entry['status'] ?? 'waiting',
                    'created_at' => $entry['created_at'] ?? now(),
                    'updated_at' => $entry['updated_at'] ?? now(),
                ],
            );
        }

        foreach ($favorites as $favorite) {
            $productId = $this->parseLegacyInteger($favorite['product_id']);

            DB::table('customer_favorites')->updateOrInsert(
                ['customer_email' => Str::lower($this->stringValue($favorite['customer_email'])), 'product_id' => $productIds[$productId]],
                ['created_at' => $favorite['created_at'] ?? now(), 'updated_at' => $favorite['updated_at'] ?? now()],
            );
        }
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
        return (int) round($this->floatValue($dollars) * 100);
    }

    private function stringValue(mixed $value): string
    {
        if (! is_string($value) && ! is_int($value) && ! is_float($value)) {
            throw new \UnexpectedValueException('Expected a string-compatible legacy value.');
        }

        return (string) $value;
    }

    private function parseLegacyInteger(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (! is_string($value) || filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new \UnexpectedValueException('Expected an integer-compatible legacy value.');
        }

        return (int) $value;
    }

    private function floatValue(mixed $value): float
    {
        if (is_float($value) || is_int($value)) {
            return $value;
        }

        if (! is_string($value) || ! is_numeric($value)) {
            throw new \UnexpectedValueException('Expected a numeric legacy value.');
        }

        return (float) $value;
    }
}
