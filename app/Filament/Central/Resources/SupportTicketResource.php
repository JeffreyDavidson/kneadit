<?php

namespace App\Filament\Central\Resources;

use App\Models\SupportTicket;
use BackedEnum;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class SupportTicketResource extends Resource
{
    protected static ?string $model = SupportTicket::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInbox;

    protected static string|UnitEnum|null $navigationGroup = 'Platform';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Support Inbox';

    public static function getNavigationBadge(): ?string
    {
        $count = SupportTicket::where('status', 'open')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            TextInput::make('subject')->required()->maxLength(255),
            Select::make('tenant_id')
                ->label('Bakery')
                ->relationship('tenant', 'store_name')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->store_name ?: $record->name)
                ->required()
                ->searchable(),
            Grid::make(2)->schema([
                Select::make('status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                    ])
                    ->default('open')
                    ->required(),
                Select::make('priority')
                    ->options([
                        'low' => 'Low',
                        'normal' => 'Normal',
                        'high' => 'High',
                    ])
                    ->default('normal')
                    ->required(),
            ]),
            Textarea::make('body')->required()->rows(5),
            Textarea::make('admin_notes')->rows(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('tenant.store_name')
                    ->label('Bakery')
                    ->placeholder('Not set')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'danger',
                        'in_progress' => 'warning',
                        'resolved' => 'success',
                        'closed' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'high' => 'danger',
                        'normal' => 'info',
                        'low' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('status', 'asc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                    ]),
                SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'normal' => 'Normal',
                        'high' => 'High',
                    ]),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make()
                    ->slideOver(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => SupportTicketResource\Pages\ListTickets::route('/'),
            'view' => SupportTicketResource\Pages\ViewTicket::route('/{record}'),
        ];
    }
}
