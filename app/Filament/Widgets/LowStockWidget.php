<?php

namespace App\Filament\Widgets;

use App\Enums\Inventory\StockStatus;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Inventory\Ingredient;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Database\Eloquent\Builder;

class LowStockWidget extends BaseWidget
{
    use HasDashboardSize;

    protected static ?int $sort = 11;

    protected static ?string $heading = 'Low Stock Ingredients';

    public static function canView(): bool
    {
        return Ingredient::query()->where(function (Builder $q) {
            $q->where('current_stock', '<=', 0)
                ->orWhereColumn('current_stock', '<=', 'low_stock_threshold');
        })->exists();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Ingredient::query()
                    ->where(function (\Illuminate\Contracts\Database\Query\Builder $q) {
                        $q->where('current_stock', '<=', 0)
                            ->orWhereColumn('current_stock', '<=', 'low_stock_threshold');
                    })
                    ->orderBy('current_stock'),
            )
            ->columns($this->columnSet())
            ->paginated(false)
            ->emptyStateHeading('All stocked up!')
            ->emptyStateIcon(Heroicon::OutlinedCheckCircle)
            ->headerActions([
                Action::make('viewAll')
                    ->label('View all')
                    ->url(route('filament.admin.resources.ingredients.index'))
                    ->view('filament.actions.view-all-link'),
            ]);
    }

    /** @return array<int, TextColumn> */
    private function columnSet(): array
    {
        // low_stock is constrained to sm/md in WidgetMeta — at sm we show
        // just the essentials (what to reorder), md adds the threshold +
        // supplier columns for context.
        $essentials = [
            TextColumn::make('name')->label('Ingredient'),
            TextColumn::make('current_stock')
                ->label('In Stock')
                ->formatStateUsing(fn (Ingredient $r) => $r->current_stock . ' ' . $r->unit)
                ->badge()
                ->color(fn (Ingredient $r) => StockStatus::resolve($r)->getColor()),
            TextColumn::make('reorder_qty')
                ->label('Reorder')
                ->getStateUsing(fn (Ingredient $r) => max(0, $r->low_stock_threshold - $r->current_stock))
                ->formatStateUsing(fn (mixed $state, Ingredient $r) => $state . ' ' . $r->unit),
        ];

        if ($this->isSize('sm')) {
            return $essentials;
        }

        return [
            ...$essentials,
            TextColumn::make('low_stock_threshold')
                ->label('Threshold')
                ->formatStateUsing(fn (Ingredient $r) => $r->low_stock_threshold . ' ' . $r->unit),
            TextColumn::make('supplier')->placeholder('—'),
        ];
    }
}
