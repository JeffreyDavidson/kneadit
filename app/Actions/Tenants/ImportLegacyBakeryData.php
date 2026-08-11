<?php

namespace App\Actions\Tenants;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportLegacyBakeryData
{
    /**
     * @param array<string, array<int, array<string, mixed>>> $data
     * @return array<string, int>
     */
    public function __invoke(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $categoryIds = $this->importCategories($data['categories'] ?? []);
            $productIds = $this->importProducts($data['products'] ?? [], $categoryIds);
            $customerIds = $this->importCustomers($data['orders'] ?? []);
            $orderIds = $this->importOrders($data['orders'] ?? [], $customerIds);

            $this->importOrderItems($data['order_items'] ?? [], $orderIds, $productIds);
            $this->importReviews($data['reviews'] ?? [], $productIds);
            $this->importSettings($data['settings'] ?? []);

            return [
                'categories' => count($categoryIds),
                'products' => count($productIds),
                'customers' => count($customerIds),
                'orders' => count($orderIds),
                'order_items' => count($data['order_items'] ?? []),
                'reviews' => count($data['reviews'] ?? []),
                'settings' => count($data['settings'] ?? []),
            ];
        });
    }

    /** @param array<int, array<string, mixed>> $categories
     * @return array<int, int>
     */
    private function importCategories(array $categories): array
    {
        $ids = [];

        foreach ($categories as $category) {
            $slug = Str::slug((string) $category['name']);
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
            $ids[(int) $category['id']] = (int) DB::table('categories')->where('slug', $slug)->value('id');
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
            $slug = Str::slug((string) $product['name']);
            DB::table('products')->updateOrInsert(
                ['slug' => $slug],
                [
                    'name' => $product['name'],
                    'description' => $product['description'] ?? null,
                    'price' => $this->cents($product['price'] ?? 0),
                    'cost' => isset($product['cost']) ? $this->cents($product['cost']) : null,
                    'category_id' => $categoryIds[(int) $product['category_id']],
                    'is_active' => $product['is_available'] ?? $product['is_active'] ?? true,
                    'is_featured' => $product['is_featured'] ?? false,
                    'image' => $product['image'] ?? null,
                    'created_at' => $product['created_at'] ?? now(),
                    'updated_at' => $product['updated_at'] ?? now(),
                ],
            );
            $ids[(int) $product['id']] = (int) DB::table('products')->where('slug', $slug)->value('id');
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
            $email = Str::lower(trim((string) $order['customer_email']));
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
            $ids[$email] = (int) DB::table('customers')->where('email', $email)->value('id');
        }

        return $ids;
    }

    /** @param array<int, array<string, mixed>> $orders
     * @param array<string, int> $customerIds
     * @return array<int, int>
     */
    private function importOrders(array $orders, array $customerIds): array
    {
        $ids = [];

        foreach ($orders as $order) {
            $email = Str::lower(trim((string) $order['customer_email']));
            DB::table('orders')->updateOrInsert(
                ['order_number' => $order['order_number']],
                [
                    'customer_id' => $customerIds[$email],
                    'status' => $order['status'] ?? 'pending',
                    'payment_status' => $order['payment_status'] ?? 'unpaid',
                    'payment_method' => $order['payment_method'] ?: 'other',
                    'subtotal' => $this->cents($order['subtotal'] ?? 0),
                    'delivery_fee' => $this->cents($order['delivery_fee'] ?? 0),
                    'discount_amount' => $this->cents($order['discount_amount'] ?? 0),
                    'tip_amount' => 0,
                    'gift_card_amount' => 0,
                    'total' => $this->cents($order['total'] ?? 0),
                    'paypal_invoice_id' => $order['paypal_invoice_id'] ?? null,
                    'delivery_address' => $order['delivery_address'] ?? null,
                    'delivery_type' => $order['fulfillment_type'] ?? 'pickup',
                    'delivery_date' => $order['requested_date'] ?? null,
                    'delivery_time' => $order['requested_time'] ?? null,
                    'notes' => $order['notes'] ?? null,
                    'created_at' => $order['created_at'] ?? now(),
                    'updated_at' => $order['updated_at'] ?? now(),
                ],
            );
            $ids[(int) $order['id']] = (int) DB::table('orders')->where('order_number', $order['order_number'])->value('id');
        }

        return $ids;
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
                'order_id' => $orderIds[(int) $item['order_id']],
                'name' => $item['product_name'],
                'product_id' => $productIds[(int) $item['product_id']] ?? null,
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
            $email = $review['email'] ?: "legacy-review-{$review['id']}@migration.invalid";
            DB::table('reviews')->updateOrInsert(
                ['customer_email' => $email, 'comment' => $review['body']],
                [
                    'customer_name' => $review['name'],
                    'product_id' => isset($review['product_id']) ? ($productIds[(int) $review['product_id']] ?? null) : null,
                    'rating' => $review['rating'],
                    'is_approved' => ($review['status'] ?? null) === 'approved',
                    'is_featured' => $review['is_featured'] ?? false,
                    'created_at' => $review['created_at'] ?? now(),
                    'updated_at' => $review['updated_at'] ?? now(),
                ],
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
            'send_review_followup_emails' => 'review_requests_enabled',
        ];

        foreach ($settings as $setting) {
            $key = $keyMap[$setting['key']] ?? $setting['key'];
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $setting['value'], 'updated_at' => now(), 'created_at' => $setting['created_at'] ?? now()],
            );
        }

        foreach (['storefront_theme' => 'classic', 'admin_theme' => 'honey', 'storefront_enabled' => '1'] as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now(), 'created_at' => now()],
            );
        }
    }

    private function cents(mixed $dollars): int
    {
        return (int) round((float) $dollars * 100);
    }
}
