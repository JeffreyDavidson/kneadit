<?php

namespace App\Actions\Tenants;

use App\Enums\Financial\CouponType;
use App\Enums\Orders\DeliveryType;
use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentMethod;
use App\Enums\Orders\PaymentStatus;
use App\Services\Settings\TenantSettingCipher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ImportLegacyBakeryData
{
    public function __construct(
        private readonly TenantSettingCipher $settingCipher,
    ) {}

    /**
     * @param array<string, array<int, array<string, mixed>>> $data
     * @return array<string, int>
     */
    public function __invoke(array $data): array
    {
        $this->validateReferences($data);

        return DB::transaction(function () use ($data): array {
            $categoryIds = $this->importCategories($data['categories'] ?? []);
            $productIds = $this->importProducts($data['products'] ?? [], $categoryIds);
            $couponIds = $this->importCoupons($data['coupons'] ?? []);
            $customerIds = $this->importCustomers($data['orders'] ?? []);
            $orderIds = $this->importOrders(
                $data['orders'] ?? [],
                $data['order_notes'] ?? [],
                $customerIds,
                $couponIds,
            );

            $this->importOrderItems($data['order_items'] ?? [], $orderIds, $productIds);
            $this->importReviews($data['reviews'] ?? [], $productIds);
            $this->importRecipes($data['recipes'] ?? [], $data['recipe_ingredients'] ?? [], $data['recipe_stages'] ?? [], $productIds);
            $this->importFinancials($data['expenses'] ?? [], $data['incomes'] ?? []);
            $this->importCapacityLimits($data['capacity_limits'] ?? []);
            $this->importHolidays($data['holidays'] ?? []);
            $this->importEngagement($data['contact_messages'] ?? [], $data['waitlist_entries'] ?? [], $data['customer_favorites'] ?? [], $productIds);
            $this->importSettings($data['settings'] ?? []);

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

    /**
     * Validate references before the transaction starts so malformed exports
     * fail clearly without partially changing the tenant database.
     *
     * @param array<string, array<int, array<string, mixed>>> $data
     */
    private function validateReferences(array $data): void
    {
        $categoryIds = $this->legacyIds($data['categories'] ?? [], 'category');
        $productIds = $this->legacyIds($data['products'] ?? [], 'product');
        $orderIds = $this->legacyIds($data['orders'] ?? [], 'order');

        foreach ($data['coupons'] ?? [] as $index => $coupon) {
            if (! array_key_exists('type', $coupon)) {
                throw new InvalidArgumentException("Coupon at index {$index} is missing a type.");
            }

            $this->couponType($coupon['type']);
        }

        foreach ($data['products'] ?? [] as $index => $product) {
            if (! array_key_exists('category_id', $product)) {
                throw new InvalidArgumentException("Product at index {$index} is missing a category ID.");
            }

            $categoryId = $this->parseLegacyInteger($product['category_id']);
            $this->assertReference($categoryIds, $categoryId, "Product at index {$index} references missing category ID {$categoryId}.");
        }

        foreach ($data['orders'] ?? [] as $index => $order) {
            if (! array_key_exists('customer_email', $order)) {
                throw new InvalidArgumentException("Order at index {$index} is missing a customer email.");
            }

            $this->orderStatus($order['status'] ?? OrderStatus::Pending->value);
            $this->paymentStatus($order['payment_status'] ?? PaymentStatus::Unpaid->value);
            $this->paymentMethod($order['payment_method'] ?? PaymentMethod::Other->value);
            $this->deliveryType($order['fulfillment_type'] ?? DeliveryType::Pickup->value);
        }

        foreach ($data['order_items'] ?? [] as $index => $item) {
            if (! array_key_exists('order_id', $item)) {
                throw new InvalidArgumentException("Order item at index {$index} is missing an order ID.");
            }

            $orderId = $this->parseLegacyInteger($item['order_id']);
            $this->assertReference($orderIds, $orderId, "Order item at index {$index} references missing order ID {$orderId}.");

            if (array_key_exists('product_id', $item) && $item['product_id'] !== null) {
                $productId = $this->parseLegacyInteger($item['product_id']);
                $this->assertReference($productIds, $productId, "Order item at index {$index} references missing product ID {$productId}.");
            }
        }
    }

    /**
     * @param array<int, array<string, mixed>> $records
     * @return array<int, true>
     */
    private function legacyIds(array $records, string $dataset): array
    {
        $ids = [];

        foreach ($records as $index => $record) {
            if (! array_key_exists('id', $record)) {
                throw new InvalidArgumentException(ucfirst($dataset) . " at index {$index} is missing an ID.");
            }

            $id = $this->parseLegacyInteger($record['id']);

            if (isset($ids[$id])) {
                throw new InvalidArgumentException("Duplicate {$dataset} ID {$id} at index {$index}.");
            }

            $ids[$id] = true;
        }

        return $ids;
    }

    /** @param array<int, true> $references */
    private function assertReference(array $references, int $id, string $message): void
    {
        if (! isset($references[$id])) {
            throw new InvalidArgumentException($message);
        }
    }

    /** @param array<int, array<string, mixed>> $coupons
     * @return array<int, int>
     */
    private function importCoupons(array $coupons): array
    {
        $ids = [];

        foreach ($coupons as $coupon) {
            $type = $this->couponType($coupon['type']);
            DB::table('coupons')->updateOrInsert(
                ['code' => Str::upper($this->stringValue($coupon['code']))],
                [
                    'type' => $type,
                    'fixed_amount' => $type === 'fixed' ? $this->cents($coupon['value']) : null,
                    'percentage' => $type === 'percentage' ? $coupon['value'] : null,
                    'min_order_amount' => isset($coupon['minimum_order']) ? $this->cents($coupon['minimum_order']) : null,
                    'max_uses' => $coupon['max_uses'] ?? null,
                    'used_count' => $coupon['times_used'] ?? 0,
                    'starts_at' => $coupon['starts_at'] ?? null,
                    'expires_at' => $coupon['expires_at'] ?? null,
                    'is_active' => $coupon['is_active'] ?? true,
                    'created_at' => $coupon['created_at'] ?? now(),
                    'updated_at' => $coupon['updated_at'] ?? now(),
                ],
            );
            $ids[$this->parseLegacyInteger($coupon['id'])] = $this->parseLegacyInteger(DB::table('coupons')->where('code', Str::upper($this->stringValue($coupon['code'])))->value('id'));
        }

        return $ids;
    }

    /** @param array<int, array<string, mixed>> $categories
     * @return array<int, int>
     */
    private function importCategories(array $categories): array
    {
        $ids = [];

        foreach ($categories as $category) {
            $slug = Str::slug($this->stringValue($category['name']));
            DB::table('categories')->updateOrInsert(
                ['slug' => $slug],
                [
                    'name' => $category['name'],
                    'description' => $category['description'] ?? null,
                    'is_active' => $category['is_active'] ?? true,
                    'sort_order' => $category['sort_order'] ?? 0,
                    'created_at' => $category['created_at'] ?? now(),
                    'updated_at' => $category['updated_at'] ?? now(),
                ],
            );
            $ids[$this->parseLegacyInteger($category['id'])] = $this->parseLegacyInteger(DB::table('categories')->where('slug', $slug)->value('id'));
        }

        return $ids;
    }

    /** @param array<int, array<string, mixed>> $products
     * @param array<int, int> $categoryIds
     * @return array<int, int>
     */
    private function importProducts(array $products, array $categoryIds): array
    {
        $ids = [];

        foreach ($products as $product) {
            $slug = Str::slug($this->stringValue($product['name']));
            DB::table('products')->updateOrInsert(
                ['slug' => $slug],
                [
                    'name' => $product['name'],
                    'description' => $product['description'] ?? null,
                    'price' => $this->cents($product['price'] ?? 0),
                    'cost' => isset($product['cost']) ? $this->cents($product['cost']) : null,
                    'category_id' => $categoryIds[$this->parseLegacyInteger($product['category_id'])],
                    'is_active' => $product['is_available'] ?? $product['is_active'] ?? true,
                    'is_featured' => $product['is_featured'] ?? false,
                    'image' => $product['image'] ?? null,
                    'created_at' => $product['created_at'] ?? now(),
                    'updated_at' => $product['updated_at'] ?? now(),
                ],
            );
            $ids[$this->parseLegacyInteger($product['id'])] = $this->parseLegacyInteger(DB::table('products')->where('slug', $slug)->value('id'));
        }

        return $ids;
    }

    /** @param array<int, array<string, mixed>> $orders
     * @return array<string, int>
     */
    private function importCustomers(array $orders): array
    {
        $ids = [];

        foreach ($orders as $order) {
            $email = Str::lower(trim($this->stringValue($order['customer_email'])));
            DB::table('customers')->updateOrInsert(
                ['email' => $email],
                [
                    'name' => $order['customer_name'],
                    'phone' => $order['customer_phone'] ?? null,
                    'address' => $order['delivery_address'] ?? null,
                    'zip' => $order['delivery_zip'] ?? null,
                    'created_at' => $order['created_at'] ?? now(),
                    'updated_at' => $order['updated_at'] ?? now(),
                ],
            );
            $ids[$email] = $this->parseLegacyInteger(DB::table('customers')->where('email', $email)->value('id'));
        }

        return $ids;
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

    /** @param array<int, array<string, mixed>> $items
     * @param array<int, int> $orderIds
     * @param array<int, int> $productIds
     */
    private function importOrderItems(array $items, array $orderIds, array $productIds): void
    {
        DB::table('order_items')->whereIn('order_id', array_values($orderIds))->delete();

        foreach ($items as $item) {
            DB::table('order_items')->insert([
                'order_id' => $orderIds[$this->parseLegacyInteger($item['order_id'])],
                'name' => $item['product_name'],
                'product_id' => $productIds[$this->parseLegacyInteger($item['product_id'])] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $this->cents($item['unit_price'] ?? 0),
                'special_instructions' => isset($item['selections']) ? json_encode($item['selections'], JSON_THROW_ON_ERROR) : null,
                'created_at' => $item['created_at'] ?? now(),
                'updated_at' => $item['updated_at'] ?? now(),
            ]);
        }
    }

    /** @param array<int, array<string, mixed>> $reviews
     * @param array<int, int> $productIds
     */
    private function importReviews(array $reviews, array $productIds): void
    {
        foreach ($reviews as $review) {
            $email = $review['email'] ?: 'legacy-review-' . $this->stringValue($review['id']) . '@migration.invalid';
            DB::table('reviews')->updateOrInsert(
                ['customer_email' => $email, 'comment' => $review['body']],
                [
                    'customer_name' => $review['name'],
                    'product_id' => isset($review['product_id']) ? ($productIds[$this->parseLegacyInteger($review['product_id'])] ?? null) : null,
                    'rating' => $review['rating'],
                    'is_approved' => ($review['status'] ?? null) === 'approved',
                    'is_featured' => $review['is_featured'] ?? false,
                    'created_at' => $review['created_at'] ?? now(),
                    'updated_at' => $review['updated_at'] ?? now(),
                ],
            );
        }
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

    /**
     * @param array<int, array<string, mixed>> $expenses
     * @param array<int, array<string, mixed>> $incomes
     */
    private function importFinancials(array $expenses, array $incomes): void
    {
        foreach ($expenses as $expense) {
            $businessPercentage = $this->parseLegacyInteger($expense['business_percentage'] ?? 100);
            DB::table('expenses')->updateOrInsert(
                ['description' => $expense['description'], 'date' => $expense['date']],
                [
                    'amount' => $this->cents($expense['amount']),
                    'category' => $expense['category'] === 'delivery_gas' ? 'delivery' : $expense['category'],
                    'receipt_image' => $expense['receipt'] ?? null,
                    'notes' => $expense['notes'] ?? null,
                    'business_percentage' => $businessPercentage,
                    'deductible_amount' => $this->cents($this->floatValue($expense['amount']) * $businessPercentage / 100),
                    'created_at' => $expense['created_at'] ?? now(),
                    'updated_at' => $expense['updated_at'] ?? now(),
                ],
            );
        }

        foreach ($incomes as $income) {
            $source = in_array($income['source'], ['farmers_market', 'cash_sale', 'paypal_direct', 'catering'], true)
                ? $income['source']
                : 'other';
            DB::table('incomes')->updateOrInsert(
                ['description' => $income['description'], 'date' => $income['date']],
                [
                    'amount' => $this->cents($income['amount']),
                    'source' => $source,
                    'notes' => $income['notes'] ?? null,
                    'created_at' => $income['created_at'] ?? now(),
                    'updated_at' => $income['updated_at'] ?? now(),
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

            if (! isset($productIds[$productId])) {
                continue;
            }

            DB::table('customer_favorites')->updateOrInsert(
                ['customer_email' => Str::lower($this->stringValue($favorite['customer_email'])), 'product_id' => $productIds[$productId]],
                ['created_at' => $favorite['created_at'] ?? now(), 'updated_at' => $favorite['updated_at'] ?? now()],
            );
        }
    }

    /** @param array<int, array<string, mixed>> $settings */
    private function importSettings(array $settings): void
    {
        $keyMap = [
            'business_name' => 'store_name',
            'tagline' => 'store_tagline',
            'default_prep_time_hours' => 'order_lead_time_hours',
            'minimum_order_amount' => 'minimum_pickup_order_amount',
            'delivery_radius_miles' => 'delivery_radius',
            'send_review_followup_emails' => 'review_requests_enabled',
        ];

        foreach ($settings as $setting) {
            $legacyKey = $this->stringValue($setting['key']);
            $key = $keyMap[$legacyKey] ?? $legacyKey;
            $value = $this->settingCipher->encrypt(
                $key,
                $this->normalizeSettingValue($key, $setting['value']),
            );
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now(), 'created_at' => $setting['created_at'] ?? now()],
            );

            if ($legacyKey === 'default_prep_time_hours') {
                $this->upsertSetting('minimum_order_lead_hours', $value);
            }

            if ($legacyKey === 'minimum_order_amount') {
                $this->upsertSetting('minimum_delivery_order_amount', $value);
            }
        }

        foreach (['storefront_theme' => 'biscotto', 'admin_theme' => 'honey', 'storefront_enabled' => '1'] as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now(), 'created_at' => now()],
            );
        }
    }

    private function normalizeSettingValue(string $key, mixed $value): mixed
    {
        if ($key === 'delivery_fee_tiers' && is_string($value) && ! str_starts_with(trim($value), '[')) {
            return json_encode($this->deliveryFeeTiers($value), JSON_THROW_ON_ERROR);
        }

        if ($key === 'operating_hours' && is_string($value) && ! str_starts_with(trim($value), '{')) {
            return json_encode([
                'monday' => ['open' => '07:00', 'close' => '18:00'],
                'tuesday' => ['open' => '07:00', 'close' => '18:00'],
                'wednesday' => ['open' => '07:00', 'close' => '18:00'],
                'thursday' => ['open' => '07:00', 'close' => '18:00'],
                'friday' => ['open' => '07:00', 'close' => '18:00'],
                'saturday' => ['open' => '08:00', 'close' => '16:00'],
                'sunday' => [],
            ], JSON_THROW_ON_ERROR);
        }

        return $value;
    }

    /** @return array<int, array{min_distance: int, max_distance: int, fee: string, description: string}> */
    private function deliveryFeeTiers(string $value): array
    {
        $tiers = [];

        foreach (explode(',', $value) as $tier) {
            if (! preg_match('/^(\d+)(?:-(\d+)|\+):(\d+(?:\.\d+)?)$/', trim($tier), $matches)) {
                continue;
            }

            $minimum = (int) $matches[1];
            $maximum = $matches[2] !== '' ? (int) $matches[2] : 999;
            $tiers[] = [
                'min_distance' => $minimum,
                'max_distance' => $maximum,
                'fee' => number_format((float) $matches[3], 2, '.', ''),
                'description' => $maximum === 999 ? "Delivery {$minimum}+ miles" : "Delivery {$minimum}–{$maximum} miles",
            ];
        }

        return $tiers;
    }

    private function upsertSetting(string $key, mixed $value): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => $key],
            ['value' => $value, 'updated_at' => now(), 'created_at' => now()],
        );
    }

    private function couponType(mixed $value): string
    {
        $normalized = Str::lower($this->stringValue($value));
        $normalized = $normalized === 'fixed_amount' ? CouponType::Fixed->value : $normalized;

        $type = CouponType::tryFrom($normalized);
        throw_if($type === null, InvalidArgumentException::class, "Unsupported coupon type [{$normalized}].");

        return $type->value;
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
