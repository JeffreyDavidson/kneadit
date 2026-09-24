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

        $stockMovementData = StockAdjustment::query()
            ->whereBetween('created_at', [$windowEnd->copy()->subDays($usageWindowDays), $windowEnd])
            ->whereIn('type', [
                StockAdjustmentType::Usage->value,
                StockAdjustmentType::Restock->value,
                StockAdjustmentType::Waste->value,
            ])
            ->select('ingredient_id')
            ->selectRaw(
                'SUM(CASE WHEN type IN (?, ?) THEN -quantity ELSE 0 END) as total_usage',
                [StockAdjustmentType::Usage->value, StockAdjustmentType::Restock->value],
            )
            ->selectRaw('SUM(-quantity) as total_depletion')
            ->groupBy('ingredient_id')
            ->toBase()
            ->get()
            ->keyBy('ingredient_id');

        $ingredients = array_values(Ingredient::query()->orderBy('name')->get()->map(function (Ingredient $i) use ($stockMovementData, $usageWindowDays): InventoryReportIngredient {
            $stockMovements = $stockMovementData->get($i->id, (object) ['total_usage' => 0, 'total_depletion' => 0]);
            $usage = $stockMovements->total_usage;
            $depletion = $stockMovements->total_depletion;
            $usageInWindow = is_numeric($usage) ? (float) $usage : 0.0;
            $depletionInWindow = is_numeric($depletion) ? (float) $depletion : 0.0;
            $dailyUsage = max(0.0, $usageInWindow / max($usageWindowDays, 1));
            $dailyDepletion = max(0.0, $depletionInWindow / max($usageWindowDays, 1));
            $daysUntilStockout = $dailyDepletion > 0 ? round($i->current_stock / $dailyDepletion, 0) : null;

            return new InventoryReportIngredient(
                name: $i->name,
                unit: $i->unit,
                currentStock: (float) $i->current_stock,
                lowStockThreshold: (float) $i->low_stock_threshold,
                isLow: $i->current_stock <= $i->low_stock_threshold,
                isOut: $i->current_stock <= 0,
                dailyUsage: round($dailyUsage, 2),
                dailyDepletion: round($dailyDepletion, 2),
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
