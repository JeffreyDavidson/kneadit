<?php

namespace App\Reports\Inventory;

use App\DataTransferObjects\Inventory\InventoryReportIngredient;
use App\DataTransferObjects\Inventory\InventoryReportResult;
use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Recipe;
use App\ValueObjects\Money;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;

class InventoryReport
{
    public function generate(): InventoryReportResult
    {
        $usageWindowDays = Config::integer('analytics.inventory_usage_window_days', 30);

        $usageData = Recipe::query()
            ->join('recipe_ingredients', 'recipes.id', '=', 'recipe_ingredients.recipe_id')
            ->join('order_items', 'order_items.product_id', '=', 'recipes.product_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.delivery_date', '>=', now()->subDays($usageWindowDays))
            ->whereDate('orders.delivery_date', '<=', today())
            ->where('orders.status', '!=', OrderStatus::Cancelled->value)
            ->where('orders.payment_status', PaymentStatus::Paid->value)
            ->selectRaw('recipe_ingredients.ingredient_id, SUM(recipe_ingredients.quantity * order_items.quantity) as total_usage')
            ->groupBy('recipe_ingredients.ingredient_id')
            ->pluck('total_usage', 'ingredient_id');

        $ingredients = array_values(Ingredient::query()->orderBy('name')->get()->map(function (Ingredient $i) use ($usageData, $usageWindowDays): InventoryReportIngredient {
            $usageInWindow = Arr::float($usageData->all(), $i->id, 0.0);
            $dailyUsage = $usageInWindow / max($usageWindowDays, 1);
            $daysUntilStockout = $dailyUsage > 0 ? round($i->current_stock / $dailyUsage, 0) : null;

            return new InventoryReportIngredient(
                name: $i->name,
                unit: $i->unit,
                currentStock: (float) $i->current_stock,
                lowStockThreshold: (float) $i->low_stock_threshold,
                isLow: $i->current_stock <= $i->low_stock_threshold,
                isOut: $i->current_stock <= 0,
                dailyUsage: round($dailyUsage, 2),
                daysUntilStockout: $daysUntilStockout,
                costPerUnit: $i->cost_per_unit ?? Money::zero(),
            );
        })->all());

        $totalItems = count($ingredients);
        $lowStockItems = collect($ingredients)->where('isLow', true)->count();
        $outOfStockItems = collect($ingredients)->where('isOut', true)->count();

        return new InventoryReportResult(
            ingredients: $ingredients,
            totalItems: $totalItems,
            lowStockItems: $lowStockItems,
            outOfStockItems: $outOfStockItems,
        );
    }
}
