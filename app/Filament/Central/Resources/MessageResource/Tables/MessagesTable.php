<?php

namespace App\Filament\Central\Resources\MessageResource\Tables;

use App\Filament\Central\Resources\MessageResource;
use App\Models\Platform\PlatformMessage;
use App\Models\Platform\Tenant;
use Filament\Actions;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class MessagesTable
{
    public static function configure(Table $table): Table
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
                    ->badge(),
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
            ->recordUrl(fn (Model $record) => MessageResource::getUrl('view', ['record' => $record->getKey()]))
            ->recordActions([
                Actions\ViewAction::make()
                    ->url(fn (Model $record) => MessageResource::getUrl('view', ['record' => $record->getKey()])),
            ]);
    }
}
