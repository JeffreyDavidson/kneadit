<?php

namespace App\Reports;

use App\Enums\PaymentStatus;
use App\Models\Ingredient;
use Illuminate\Support\Facades\DB;

class InventoryReport
{
    /** @return array<string, mixed> */
    public function generate(): array
    {
        $usageData = DB::table('recipe_ingredients')
            ->join('recipes', 'recipes.id', '=', 'recipe_ingredients.recipe_id')
            ->join('order_items', 'order_items.product_id', '=', 'recipes.product_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.delivery_date', '>=', now()->subDays(config('analytics.inventory_usage_window_days', 30)))
            ->where('orders.payment_status', PaymentStatus::Paid->value)
            ->selectRaw('recipe_ingredients.ingredient_id, SUM(recipe_ingredients.quantity * order_items.quantity) as total_usage')
            ->groupBy('recipe_ingredients.ingredient_id')
            ->pluck('total_usage', 'ingredient_id');

        $ingredients = Ingredient::query()->orderBy('name')->get()->map(function (Ingredient $i) use ($usageData) {
            $usageLast30 = (float) ($usageData[$i->id] ?? 0);
            $dailyUsage = $usageLast30 / 30;
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
                'cost_per_unit' => (float) $i->cost_per_unit,
            ];
        })->all();

        $totalItems = count($ingredients);
        $lowStockItems = collect($ingredients)->where('is_low', true)->count();
        $outOfStockItems = collect($ingredients)->where('is_out', true)->count();

        return ['ingredients' => $ingredients, 'totalItems' => $totalItems, 'lowStockItems' => $lowStockItems, 'outOfStockItems' => $outOfStockItems];
    }
}
