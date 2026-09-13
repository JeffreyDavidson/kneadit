<?php

namespace App\Actions\Tenants;

use App\DataTransferObjects\Tenants\LegacyBakeryImportData;
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
     * @param LegacyBakeryImportData|array<string, array<int, array<string, mixed>>> $data
     * @return array<string, int>
     */
    public function __invoke(LegacyBakeryImportData|array $data): array
    {
        $importData = $data instanceof LegacyBakeryImportData
            ? $data
            : LegacyBakeryImportData::from($data);
        $datasets = $importData->toArray();

        ($this->validator)($datasets);

        return DB::transaction(function () use ($datasets): array {
            $catalogIds = $this->catalogImporter->import(
                $datasets['categories'] ?? [],
                $datasets['products'] ?? [],
            );
            $categoryIds = $catalogIds['category_ids'];
            $productIds = $catalogIds['product_ids'];
            $couponIds = $this->couponImporter->import($datasets['coupons'] ?? []);
            $customerIds = $this->customerImporter->import($datasets['orders'] ?? []);
            $orderIds = $this->orderImporter->import(
                $datasets['orders'] ?? [],
                $datasets['order_notes'] ?? [],
                $customerIds,
                $couponIds,
            );

            $this->orderItemImporter->import($datasets['order_items'] ?? [], $orderIds, $productIds);
            $this->reviewImporter->import($datasets['reviews'] ?? [], $productIds, $orderIds);
            $this->recipeImporter->import($datasets['recipes'] ?? [], $datasets['recipe_ingredients'] ?? [], $datasets['recipe_stages'] ?? [], $productIds);
            $this->financialImporter->import($datasets['expenses'] ?? [], $datasets['incomes'] ?? []);
            $this->schedulingImporter->import($datasets['capacity_limits'] ?? [], $datasets['holidays'] ?? []);
            $this->engagementImporter->import($datasets['contact_messages'] ?? [], $datasets['waitlist_entries'] ?? [], $datasets['customer_favorites'] ?? [], $productIds);
            $this->settingsImporter->import($datasets['settings'] ?? []);

            return [
                'categories' => count($categoryIds),
                'products' => count($productIds),
                'coupons' => count($couponIds),
                'customers' => count($customerIds),
                'orders' => count($orderIds),
                'order_notes' => count($datasets['order_notes'] ?? []),
                'order_items' => count($datasets['order_items'] ?? []),
                'reviews' => count($datasets['reviews'] ?? []),
                'recipes' => count($datasets['recipes'] ?? []),
                'expenses' => count($datasets['expenses'] ?? []),
                'incomes' => count($datasets['incomes'] ?? []),
                'capacity_limits' => count($datasets['capacity_limits'] ?? []),
                'holidays' => count($datasets['holidays'] ?? []),
                'contact_messages' => count($datasets['contact_messages'] ?? []),
                'waitlist_entries' => count($datasets['waitlist_entries'] ?? []),
                'customer_favorites' => count($datasets['customer_favorites'] ?? []),
                'settings' => count($datasets['settings'] ?? []),
            ];
        });
    }
}
