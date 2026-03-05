<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class BakingSheetWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected static ?string $heading = 'Daily Baking Sheet';

    protected int|string|array $columnSpan = 'full';

    public function getTableRecordKey(\Illuminate\Database\Eloquent\Model|array $record): string
    {
        return $record->product_name ?? '';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                OrderItem::query()
                    ->selectRaw('product_name, SUM(quantity) as total_quantity')
                    ->whereHas('order', function (Builder $query) {
                        $query->whereIn('status', ['pending', 'confirmed', 'baking'])
                            ->where(function (Builder $q) {
                                $q->whereDate('requested_date', Carbon::today())
                                    ->orWhere(function (Builder $q2) {
                                        $q2->whereDate('requested_date', '>', Carbon::today())
                                            ->where('status', 'confirmed');
                                    });
                            });
                    })
                    ->groupBy('product_name')
            )
            ->columns([
                TextColumn::make('product_name')
                    ->label('Product')
                    ->sortable(),
                TextColumn::make('total_quantity')
                    ->label('Qty Needed')
                    ->sortable()
                    ->badge()
                    ->color('primary'),
            ])
            ->defaultSort('total_quantity', 'desc')
            ->emptyStateHeading('Nothing to bake!')
            ->emptyStateIcon('heroicon-o-cake')
            ->paginated(false);
    }
}
