<?php

namespace App\Filament\Central\Widgets;

use App\Filament\Central\Resources\TenantResource;
use App\Models\Platform\Tenant;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentTenants extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Recent Signups';

    public function table(Table $table): Table
    {
        return $table
            ->query(Tenant::query()->latest()->limit(5))
            ->recordUrl(fn (Tenant $record) => TenantResource::getUrl('view', ['record' => $record->id]))
            ->columns([
                TextColumn::make('store_name')
                    ->label('Bakery')
                    ->placeholder('Not set')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Owner'),

                TextColumn::make('email')
                    ->toggleable(),

                TextColumn::make('plan')
                    ->badge(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Joined')
                    ->since(),
            ])
            ->paginated(false);
    }
}
