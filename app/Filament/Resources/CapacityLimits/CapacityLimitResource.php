<?php

namespace App\Filament\Resources\CapacityLimits;

use App\Filament\Traits\RequiresRole;
use App\Models\CapacityLimit;
use App\Traits\HasPlanGating;
use BackedEnum;
use Filament\Actions;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
            Section::make('Capacity Limit')
                ->icon('heroicon-o-clock')
                ->description('Set order limits for a specific day or recurring weekday')
                ->columns(1)
                ->columnSpanFull()
                ->components([
                    Select::make('day_type')
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
                        ->afterStateHydrated(function (mixed $component, ?CapacityLimit $record) {
                            if (! $record) {
                                return;
                            }
                            if ($record->specific_date) {
                                $component->state('specific');
                            } else {
                                $component->state((string) $record->day_of_week);
                            }
                        })
                        ->dehydrated(false)
                        ->afterStateUpdated(function (?string $state, Set $set) {
                            if ($state === 'specific') {
                                $set('day_of_week', null);
                            } else {
                                $set('day_of_week', (int) $state);
                                $set('specific_date', null);
                            }
                        }),

                    DatePicker::make('specific_date')
                        ->label('Date')
                        ->visible(fn (Get $get) => $get('day_type') === 'specific')
                        ->required(fn (Get $get) => $get('day_type') === 'specific')
                        ->native(false),

                    Hidden::make('day_of_week'),

                    TextInput::make('max_orders')
                        ->label('Max Orders')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->prefixIcon('heroicon-o-shopping-bag')
                        ->helperText('0 = unlimited (unless blocked)'),

                    Toggle::make('is_blocked')
                        ->label('Block Day Entirely')
                        ->helperText('No orders allowed at all on this day'),

                    Textarea::make('notes')
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
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderByRaw('COALESCE(specific_date, day_of_week) '.$direction))
                    ->getStateUsing(function (CapacityLimit $record) use ($dayNames) {
                        if ($record->specific_date) {
                            return $record->specific_date->format('D, M j, Y');
                        }

                        return $dayNames[$record->day_of_week] ?? '—';
                    }),

                Tables\Columns\TextColumn::make('max_orders')
                    ->label('Max Orders')
                    ->formatStateUsing(fn (int $state) => $state > 0 ? $state : 'Unlimited')
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
                EditAction::make()->slideOver()->modalWidth('md'),
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
