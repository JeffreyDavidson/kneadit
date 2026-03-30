<?php

namespace App\Filament\Central\Resources;

use App\Enums\PlatformSenderType;
use App\Filament\Central\Resources\MessageResource\Pages\ListMessages;
use App\Filament\Central\Resources\MessageResource\Pages\ViewMessage;
use App\Models\PlatformMessage;
use App\Models\Tenant;
use BackedEnum;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
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
                    ->weight(FontWeight::Bold),
                TextColumn::make('body')
                    ->label('Preview')
                    ->limit(60)
                    ->color('gray'),
                IconColumn::make('is_read')
                    ->label('Read')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedCheckCircle)
                    ->falseIcon(Heroicon::OutlinedEnvelope)
                    ->trueColor('success')
                    ->falseColor('warning'),
                TextColumn::make('sender_type')
                    ->label('From')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        PlatformSenderType::Admin->value => 'info',
                        PlatformSenderType::Tenant->value => 'warning',
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
                    ->options(fn () => Tenant::query()->get()->mapWithKeys(fn (Tenant $t) => [$t->id => $t->store_name ?: $t->name])->all())
                    ->searchable(),
            ])
            ->defaultSort('is_read', 'asc')
            ->recordUrl(fn (Model $record) => static::getUrl('view', ['record' => $record->getKey()]))
            ->actions([
                Actions\ViewAction::make()
                    ->url(fn (Model $record) => static::getUrl('view', ['record' => $record->getKey()])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMessages::route('/'),
            'view' => ViewMessage::route('/{record}'),
        ];
    }
}
