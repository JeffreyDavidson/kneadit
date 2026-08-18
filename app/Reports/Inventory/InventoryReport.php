<?php

namespace App\Reports\Inventory;

use App\Enums\Orders\PaymentStatus;
use App\Models\Inventory\Ingredient;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class InventoryReport
{
    /** @return array<string, mixed> */
    public function generate(): array
    {
        $usageWindowDays = Config::integer('analytics.inventory_usage_window_days', 30);

        $usageData = DB::table('recipe_ingredients')
            ->join('recipes', 'recipes.id', '=', 'recipe_ingredients.recipe_id')
            ->join('order_items', 'order_items.product_id', '=', 'recipes.product_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.delivery_date', '>=', now()->subDays($usageWindowDays))
            ->where('orders.payment_status', PaymentStatus::Paid->value)
            ->selectRaw('recipe_ingredients.ingredient_id, SUM(recipe_ingredients.quantity * order_items.quantity) as total_usage')
            ->groupBy('recipe_ingredients.ingredient_id')
            ->pluck('total_usage', 'ingredient_id');

        $ingredients = Ingredient::query()->orderBy('name')->get()->map(function (Ingredient $i) use ($usageData, $usageWindowDays) {
            $usageLast30 = Arr::float($usageData->all(), $i->id, 0.0);
            $dailyUsage = $usageLast30 / max($usageWindowDays, 1);
            $daysUntilStockout = $dailyUsage > 0 ? round($i->current_stock / $dailyUsage, 0) : null;

            return [
                'name' => $i->name,
                'unit' => $i->unit,
                'current_stock' => (float) $i->current_stock,
                'low_stock_threshold' => (float) $i->low_stock_threshold,
                'is_low' => $i->current_stock <= $i->low_stock_threshold,
                'is_out' => $i->current_stock <= 0,
                'daily_usage' => round($dailyUsage, 2),
                'days_until_stockout' => $daysUntilStockout,
                'cost_per_unit' => $i->cost_per_unit?->dollars() ?? 0.0,
            ];
        })->all();

        $totalItems = count($ingredients);
        $lowStockItems = collect($ingredients)->where('is_low', true)->count();
        $outOfStockItems = collect($ingredients)->where('is_out', true)->count();

        return ['ingredients' => $ingredients, 'totalItems' => $totalItems, 'lowStockItems' => $lowStockItems, 'outOfStockItems' => $outOfStockItems];
    }
}
