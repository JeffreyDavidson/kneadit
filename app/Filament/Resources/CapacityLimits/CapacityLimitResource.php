<?php

namespace App\Filament\Resources\CapacityLimits;

use App\Filament\Resources\CapacityLimits\Pages;
use App\Models\CapacityLimit;
use BackedEnum;
use Filament\Actions;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use App\Filament\Traits\RequiresRole;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

use App\Traits\HasPlanGating;
class CapacityLimitResource extends Resource
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): string
    {
        return 'manager';
    }


    protected static string $requiredPlan = 'pro';
    protected static ?string $model = CapacityLimit::class;

    protected static ?string $navigationLabel = 'Capacity Limits';

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return 'heroicon-o-clock';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            \Filament\Schemas\Components\Section::make('Capacity Limit')
                ->icon('heroicon-o-clock')
                ->description('Set order limits for a specific day or recurring weekday')
                ->columns(2)
                ->columnSpanFull()
                ->components([
                    \Filament\Forms\Components\Select::make('day_type')
                        ->label('Day')
                        ->options([
                            '0' => 'Monday',
                            '1' => 'Tuesday',
                            '2' => 'Wednesday',
                            '3' => 'Thursday',
                            '4' => 'Friday',
                            '5' => 'Saturday',
                            '6' => 'Sunday',
                            'specific' => 'Specific Date',
                        ])
                        ->required()
                        ->live()
                        ->afterStateHydrated(function ($component, $record) {
                            if (!$record) return;
                            if ($record->specific_date) {
                                $component->state('specific');
                            } else {
                                $component->state((string) $record->day_of_week);
                            }
                        })
                        ->dehydrated(false)
                        ->afterStateUpdated(function ($state, $set) {
                            if ($state === 'specific') {
                                $set('day_of_week', null);
                            } else {
                                $set('day_of_week', (int) $state);
                                $set('specific_date', null);
                            }
                        }),

                    \Filament\Forms\Components\DatePicker::make('specific_date')
                        ->label('Date')
                        ->visible(fn ($get) => $get('day_type') === 'specific')
                        ->required(fn ($get) => $get('day_type') === 'specific')
                        ->native(false),

                    \Filament\Forms\Components\Hidden::make('day_of_week'),

                    \Filament\Forms\Components\TextInput::make('max_orders')
                        ->label('Max Orders')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->prefixIcon('heroicon-o-shopping-bag')
                        ->helperText('0 = unlimited (unless blocked)'),

                    \Filament\Forms\Components\Toggle::make('is_blocked')
                        ->label('Block Day Entirely')
                        ->helperText('No orders allowed at all on this day'),

                    \Filament\Forms\Components\Textarea::make('notes')
                        ->rows(2)
                        ->maxLength(500)
                        ->placeholder('Optional notes (e.g. "Holiday closure")')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        $dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        return $table
            ->heading('Capacity Limits')
            ->emptyStateHeading('No capacity limits set')
            ->emptyStateDescription('All days are open for orders.')
            ->emptyStateIcon('heroicon-o-clock')
            ->columns([
                Tables\Columns\TextColumn::make('day_label')
                    ->label('Day / Date')
                    ->sortable(query: fn ($query, $direction) => $query->orderByRaw('COALESCE(specific_date, day_of_week) ' . $direction))
                    ->getStateUsing(function (CapacityLimit $record) use ($dayNames) {
                        if ($record->specific_date) {
                            return $record->specific_date->format('D, M j, Y');
                        }
                        return $dayNames[$record->day_of_week] ?? '—';
                    }),

                Tables\Columns\TextColumn::make('max_orders')
                    ->label('Max Orders')
                    ->formatStateUsing(fn ($state) => $state > 0 ? $state : 'Unlimited')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_blocked')
                    ->label('Blocked')
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),

                Tables\Columns\TextColumn::make('notes')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->defaultSort('day_of_week')
            ->actions([
                EditAction::make()->slideOver()->modalWidth('2xl'),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCapacityLimits::route('/'),
        ];
    }
}
