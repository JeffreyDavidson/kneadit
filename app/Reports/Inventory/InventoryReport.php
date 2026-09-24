<?php

namespace App\Reports\Inventory;

use App\DataTransferObjects\Inventory\InventoryReportIngredient;
use App\DataTransferObjects\Inventory\InventoryReportResult;
use App\Enums\Inventory\StockAdjustmentType;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\StockAdjustment;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\Config;

class InventoryReport
{
    public function generate(): InventoryReportResult
    {
        $usageWindowDays = Config::integer('analytics.inventory_usage_window_days', 30);
        $windowEnd = now();

        $usageData = StockAdjustment::query()
            ->whereBetween('created_at', [$windowEnd->copy()->subDays($usageWindowDays), $windowEnd])
            ->whereIn('type', [StockAdjustmentType::Usage->value, StockAdjustmentType::Restock->value])
            ->selectRaw('ingredient_id, SUM(-quantity) as total_usage')
            ->groupBy('ingredient_id')
            ->pluck('total_usage', 'ingredient_id');

        $ingredients = array_values(Ingredient::query()->orderBy('name')->get()->map(function (Ingredient $i) use ($usageData, $usageWindowDays): InventoryReportIngredient {
            $usage = $usageData->get($i->id, 0);
            $usageInWindow = is_numeric($usage) ? (float) $usage : 0.0;
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
