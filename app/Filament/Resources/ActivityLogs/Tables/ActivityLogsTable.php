<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use App\Enums\Operations\ActivityAction;
use App\Models\Operations\ActivityLog;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime()
                    ->since()
                    ->tooltip(fn (ActivityLog $record): ?string => $record->created_at?->format('M j, Y g:i A'))
                    ->sortable(),

                TextColumn::make('action')
                    ->badge(),

                TextColumn::make('user_name')
                    ->label('Actor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('model_type')
                    ->label('Model')
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '—')
                    ->toggleable(),

                TextColumn::make('model_id')
                    ->label('ID')
                    ->numeric()
                    ->toggleable(),

                TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('action')
                    ->options(ActivityAction::class),

                SelectFilter::make('model_type')
                    ->label('Model')
                    ->options(fn (): array => ActivityLog::query()
                        ->whereNotNull('model_type')
                        ->distinct()
                        ->orderBy('model_type')
                        ->pluck('model_type')
                        ->mapWithKeys(fn (string $fqcn): array => [$fqcn => class_basename($fqcn)])
                        ->all()),

                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, string $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, string $date) => $q->whereDate('created_at', '<=', $date));
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

                Filter::make('actor')
                    ->schema([
                        Select::make('user_name')
                            ->options(fn (): array => ActivityLog::query()
                                ->distinct()
                                ->orderBy('user_name')
                                ->pluck('user_name', 'user_name')
                                ->all())
                            ->searchable(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['user_name'] ?? null, fn (Builder $q, string $name) => $q->where('user_name', $name));
                    })
                    ->indicateUsing(function (array $data): array {
                        return ($data['user_name'] ?? null)
                            ? [Indicator::make("Actor: {$data['user_name']}")->removeField('user_name')]
                            : [];
                    }),
            ])
            ->emptyStateHeading('No activity yet')
            ->emptyStateDescription('Model creates, updates, and deletes will appear here as they happen.');
    }
}
