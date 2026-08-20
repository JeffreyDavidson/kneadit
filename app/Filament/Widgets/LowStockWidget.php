<?php

namespace App\Filament\Widgets;

use App\Enums\Inventory\StockStatus;
use App\Filament\Resources\Ingredients\IngredientResource;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Inventory\Ingredient;
use Filament\Widgets\Widget;

class LowStockWidget extends Widget
{
    use HasDashboardSize;

    protected static ?int $sort = 11;

    protected string $view = 'filament.widgets.low-stock';

    public static function canView(): bool
    {
        return Ingredient::query()->lowStock()->exists();
    }

    /** @return array<int, array<string, mixed>> */
    public function getRows(): array
    {
        return Ingredient::query()->lowStock()
            ->orderBy('current_stock')
            ->get()
            ->map(fn (Ingredient $ingredient): array => [
                'id' => $ingredient->id,
                'name' => $ingredient->name,
                'current_stock' => $ingredient->current_stock,
                'unit' => $ingredient->unit,
                'reorder_qty' => max(0, $ingredient->low_stock_threshold - $ingredient->current_stock),
                'threshold' => $ingredient->low_stock_threshold,
                'supplier' => $ingredient->supplier,
                'status_color' => $this->statusColor(StockStatus::resolve($ingredient)),
            ])
            ->all();
    }

    public function getViewAllUrl(): string
    {
        return IngredientResource::getUrl('index');
    }

    private function statusColor(StockStatus $status): string
    {
        return match ($status) {
            StockStatus::Out => '#dc2626',
            StockStatus::Low => '#e8b04a',
            StockStatus::Good => '#6b9e3a',
        };
    }
}
