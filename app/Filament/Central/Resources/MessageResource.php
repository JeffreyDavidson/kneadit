<?php

namespace App\Filament\Central\Resources;

use App\Models\PlatformMessage;
use App\Models\Tenant;
use BackedEnum;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class MessageResource extends Resource
{
    protected static ?string $model = PlatformMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'Communication';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Messages';

    protected static ?string $modelLabel = 'Message';

    protected static ?string $pluralModelLabel = 'Messages';

    public static function getNavigationBadge(): ?string
    {
        $count = PlatformMessage::topLevel()->fromTenant()->unread()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(PlatformMessage::query()->topLevel())
            ->columns([
                TextColumn::make('tenant.store_name')
                    ->label('Bakery')
                    ->placeholder('Not set')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('body')
                    ->label('Preview')
                    ->limit(60)
                    ->color('gray'),
                IconColumn::make('is_read')
                    ->label('Read')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-envelope')
                    ->trueColor('success')
                    ->falseColor('warning'),
                TextColumn::make('sender_type')
                    ->label('From')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'admin' => 'info',
                        'tenant' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Received')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_read')
                    ->label('Read status')
                    ->trueLabel('Read')
                    ->falseLabel('Unread')
                    ->placeholder('All'),
                SelectFilter::make('tenant_id')
                    ->label('Bakery')
                    ->options(fn () => Tenant::get()->mapWithKeys(fn ($t) => [$t->id => $t->store_name ?: $t->name])->toArray())
                    ->searchable(),
            ])
            ->defaultSort('is_read', 'asc')
            ->recordUrl(fn ($record) => static::getUrl('view', ['record' => $record->getKey()]))
            ->actions([
                Actions\ViewAction::make()
                    ->url(fn ($record) => static::getUrl('view', ['record' => $record->getKey()])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Central\Resources\MessageResource\Pages\ListMessages::route('/'),
            'compose' => \App\Filament\Central\Resources\MessageResource\Pages\ComposeMessage::route('/compose'),
            'view' => \App\Filament\Central\Resources\MessageResource\Pages\ViewMessage::route('/{record}'),
        ];
    }
}
