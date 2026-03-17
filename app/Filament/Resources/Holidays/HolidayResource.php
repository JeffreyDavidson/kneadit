<?php

namespace App\Filament\Resources\Holidays;

use App\Filament\Traits\RequiresRole;
use App\Models\Holiday;
use App\Traits\HasPlanGating;
use BackedEnum;
use Filament\Actions;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Date;

class HolidayResource extends Resource
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): string
    {
        return 'manager';
    }

    protected static string $requiredPlan = 'pro';

    protected static ?string $model = Holiday::class;

    protected static ?string $navigationLabel = 'Holidays';

    protected static ?int $navigationSort = 9;

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return 'heroicon-o-sun';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Tools';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Holiday Details')
                ->icon('heroicon-o-calendar-days')
                ->description('Plan for upcoming holiday orders')
                ->columns(1)
                ->columnSpanFull()
                ->components([
                    TextInput::make('name')
                        ->label('Holiday Name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('e.g. Thanksgiving, Valentine\'s Day'),

                    DatePicker::make('date')
                        ->label('Holiday Date')
                        ->required()
                        ->native(false),

                    DatePicker::make('order_deadline')
                        ->label('Order Deadline')
                        ->required()
                        ->native(false)
                        ->helperText('Last day customers can place orders'),

                    DatePicker::make('prep_start')
                        ->label('Prep Start Date')
                        ->native(false)
                        ->helperText('When to begin prepping for this holiday'),

                    TextInput::make('max_orders')
                        ->label('Max Orders')
                        ->numeric()
                        ->minValue(1)
                        ->prefixIcon('heroicon-o-shopping-bag')
                        ->helperText('Leave blank for no limit'),

                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),

                    Textarea::make('notes')
                        ->label('Notes')
                        ->rows(3)
                        ->maxLength(1000)
                        ->placeholder('Special menu items, instructions, etc.')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Holidays')
            ->emptyStateHeading('No holidays planned')
            ->emptyStateDescription('Add upcoming holidays to track order deadlines.')
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->defaultSort('date')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Holiday')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date('D, M j, Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('order_deadline')
                    ->label('Deadline')
                    ->date('M j')
                    ->sortable(),

                Tables\Columns\TextColumn::make('orders_display')
                    ->label('Orders')
                    ->getStateUsing(function (Holiday $record) {
                        $count = $record->orderCount();

                        return $record->max_orders
                            ? "{$count} / {$record->max_orders}"
                            : (string) $count;
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function (Holiday $record) {
                        if ($record->date->isPast()) {
                            return 'Past';
                        }
                        if ($record->isDeadlinePassed()) {
                            return 'Deadline passed';
                        }
                        $days = $record->daysUntilDeadline();
                        if ($days <= 3) {
                            return 'Urgent';
                        }

                        return 'Open';
                    })
                    ->color(fn (string $state) => match ($state) {
                        'Past' => 'gray',
                        'Deadline passed' => 'danger',
                        'Urgent' => 'warning',
                        'Open' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('days_until')
                    ->label('Days Until')
                    ->getStateUsing(function (Holiday $record) {
                        if ($record->date->isPast()) {
                            return 'Passed';
                        }
                        $days = (int) Date::today()->diffInDays($record->date, false);

                        return "{$days}d";
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
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
            'index' => Pages\ListHolidays::route('/'),
        ];
    }
}
