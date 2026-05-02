<?php

namespace App\Filament\Resources\WebhookDeliveries\Tables;

use App\Actions\Operations\RedeliverWebhook;
use App\Models\Operations\WebhookDelivery;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WebhookDeliveriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('dispatched_at')
                    ->label('When')
                    ->dateTime()
                    ->since()
                    ->tooltip(fn (WebhookDelivery $record): ?string => $record->dispatched_at?->format('M j, Y g:i:s A'))
                    ->sortable(),

                TextColumn::make('event')
                    ->badge()
                    ->searchable(),

                TextColumn::make('status_code')
                    ->label('HTTP')
                    ->badge()
                    ->placeholder('—')
                    ->color(fn (?int $state): string => match (true) {
                        $state === null => 'gray',
                        $state >= 200 && $state < 300 => 'success',
                        $state >= 400 && $state < 500 => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),

                IconColumn::make('succeeded')
                    ->label('OK')
                    ->boolean(),

                TextColumn::make('attempt')
                    ->label('Attempt')
                    ->numeric()
                    ->toggleable(),

                TextColumn::make('url')
                    ->limit(45)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('responded_at')
                    ->label('Responded')
                    ->dateTime()
                    ->since()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('dispatched_at', 'desc')
            ->filters([
                SelectFilter::make('event')
                    ->options(fn (): array => WebhookDelivery::query()
                        ->distinct()
                        ->orderBy('event')
                        ->pluck('event', 'event')
                        ->all()),

                TernaryFilter::make('succeeded')
                    ->label('Status')
                    ->boolean()
                    ->trueLabel('Succeeded')
                    ->falseLabel('Failed')
                    ->native(false),

                Filter::make('dispatched_at')
                    ->schema([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, string $date) => $q->whereDate('dispatched_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, string $date) => $q->whereDate('dispatched_at', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators[] = Indicator::make("From {$data['from']}")->removeField('from');
                        }
                        if ($data['until'] ?? null) {
                            $indicators[] = Indicator::make("Until {$data['until']}")->removeField('until');
                        }

                        return $indicators;
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->slideOver()
                    ->modalHeading(fn (WebhookDelivery $record): string => "Webhook delivery — {$record->event}")
                    ->schema(fn (Schema $schema): Schema => $schema->components([
                        Section::make('Request')
                            ->schema([
                                TextEntry::make('url')->label('URL'),
                                TextEntry::make('event')->badge(),
                                TextEntry::make('signature')->copyable(),
                                TextEntry::make('payload')
                                    ->label('Payload')
                                    ->formatStateUsing(fn (mixed $state): string => json_encode($state, JSON_PRETTY_PRINT) ?: '—')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Section::make('Response')
                            ->schema([
                                TextEntry::make('status_code')
                                    ->label('HTTP Status')
                                    ->badge()
                                    ->placeholder('—'),
                                TextEntry::make('attempt'),
                                IconEntry::make('succeeded')
                                    ->label('Succeeded')
                                    ->boolean(),
                                TextEntry::make('responded_at')
                                    ->label('Responded At')
                                    ->dateTime()
                                    ->placeholder('—'),
                                TextEntry::make('response_body')
                                    ->label('Response Body')
                                    ->placeholder('—')
                                    ->columnSpanFull(),
                                TextEntry::make('error')
                                    ->label('Error')
                                    ->placeholder('—')
                                    ->visible(fn (WebhookDelivery $record): bool => filled($record->error))
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                    ])),

                Action::make('redeliver')
                    ->label('Redeliver')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Redeliver this webhook?')
                    ->modalDescription(fn (WebhookDelivery $record): string => "Re-sends the original {$record->event} payload to the currently configured webhook URL. A new delivery row will be recorded.")
                    ->action(fn (WebhookDelivery $record) => resolve(RedeliverWebhook::class)($record))
                    ->successNotificationTitle('Webhook redelivered'),
            ])
            ->emptyStateHeading('No webhook deliveries yet')
            ->emptyStateDescription('Once you configure a webhook URL in Settings, every dispatched event will appear here.');
    }
}
