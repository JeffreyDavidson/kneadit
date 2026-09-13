<?php

namespace App\Actions\Tenants;

use App\Services\Tenants\Contracts\LegacyCatalogImporter;
use App\Services\Tenants\Contracts\LegacyCouponImporter;
use App\Services\Tenants\Contracts\LegacyCustomerImporter;
use App\Services\Tenants\Contracts\LegacyEngagementImporter;
use App\Services\Tenants\Contracts\LegacyFinancialImporter;
use App\Services\Tenants\Contracts\LegacyOrderImporter;
use App\Services\Tenants\Contracts\LegacyOrderItemImporter;
use App\Services\Tenants\Contracts\LegacyRecipeImporter;
use App\Services\Tenants\Contracts\LegacyReviewImporter;
use App\Services\Tenants\Contracts\LegacySchedulingImporter;
use App\Services\Tenants\Contracts\LegacySettingsImporter;
use Illuminate\Support\Facades\DB;

class ImportLegacyBakeryData
{
    public function __construct(
        private readonly LegacyBakeryDataValidator $validator,
        private readonly LegacyCatalogImporter $catalogImporter,
        private readonly LegacyCouponImporter $couponImporter,
        private readonly LegacyCustomerImporter $customerImporter,
        private readonly LegacyOrderImporter $orderImporter,
        private readonly LegacyFinancialImporter $financialImporter,
        private readonly LegacyEngagementImporter $engagementImporter,
        private readonly LegacyOrderItemImporter $orderItemImporter,
        private readonly LegacyReviewImporter $reviewImporter,
        private readonly LegacyRecipeImporter $recipeImporter,
        private readonly LegacySchedulingImporter $schedulingImporter,
        private readonly LegacySettingsImporter $settingsImporter,
    ) {}

    /**
     * @param array<string, array<int, array<string, mixed>>> $data
     * @return array<string, int>
     */
    public function __invoke(array $data): array
    {
        ($this->validator)($data);

        return DB::transaction(function () use ($data): array {
            $catalogIds = $this->catalogImporter->import(
                $data['categories'] ?? [],
                $data['products'] ?? [],
            );
            $categoryIds = $catalogIds['category_ids'];
            $productIds = $catalogIds['product_ids'];
            $couponIds = $this->couponImporter->import($data['coupons'] ?? []);
            $customerIds = $this->customerImporter->import($data['orders'] ?? []);
            $orderIds = $this->orderImporter->import(
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
}
