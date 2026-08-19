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
use Illuminate\Support\Arr;

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
                    ->tooltip(fn (ActivityLog $record): string => $record->created_at->format('M j, Y g:i A'))
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
                        ->mapWithKeys(function (mixed $fqcn): array {
                            if (! is_string($fqcn)) {
                                throw new \UnexpectedValueException('Activity model types must be strings.');
                            }

                            return [$fqcn => class_basename($fqcn)];
                        })
                        ->all()),

                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $from = Arr::string($data, 'from', '');
                        $until = Arr::string($data, 'until', '');

                        return $query
                            ->when($from !== '', fn (Builder $q) => $q->whereDate('created_at', '>=', $from))
                            ->when($until !== '', fn (Builder $q) => $q->whereDate('created_at', '<=', $until));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        $from = Arr::string($data, 'from', '');
                        $until = Arr::string($data, 'until', '');

                        if ($from !== '') {
                            $indicators[] = Indicator::make("From {$from}")->removeField('from');
                        }
                        if ($until !== '') {
                            $indicators[] = Indicator::make("Until {$until}")->removeField('until');
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
                                ->filter(fn (mixed $value): bool => is_string($value) && $value !== '')
                                ->all())
                            ->searchable(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $name = Arr::string($data, 'user_name', '');

                        return $query->when($name !== '', fn (Builder $q) => $q->where('user_name', $name));
                    })
                    ->indicateUsing(function (array $data): array {
                        $name = Arr::string($data, 'user_name', '');

                        return $name !== ''
                            ? [Indicator::make("Actor: {$name}")->removeField('user_name')]
                            : [];
                    }),
            ])
            ->emptyStateHeading('No activity yet')
            ->emptyStateDescription('Model creates, updates, and deletes will appear here as they happen.');
    }
}
