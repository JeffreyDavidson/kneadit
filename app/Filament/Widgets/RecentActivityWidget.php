<?php

namespace App\Filament\Widgets;

use App\Models\Operations\ActivityLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentActivityWidget extends BaseWidget
{
    protected static ?int $sort = 13;

    protected static ?string $heading = 'Recent Activity';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ActivityLog::query()->latest()->limit(5),
            )
            ->columns([
                TextColumn::make('created_at')
                    ->label('When')
                    ->since()
                    ->sortable(),
                TextColumn::make('user_name')
                    ->label('User'),
                TextColumn::make('action')
                    ->badge(),
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(60),
            ])
            ->paginated(false)
            ->defaultPaginationPageOption(5);
    }
}
