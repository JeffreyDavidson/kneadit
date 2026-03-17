<?php

namespace App\Filament\Resources\LoyaltyRewards;

use App\Filament\Resources\LoyaltyRewards\Pages\CreateLoyaltyReward;
use App\Filament\Resources\LoyaltyRewards\Pages\EditLoyaltyReward;
use App\Filament\Resources\LoyaltyRewards\Pages\ListLoyaltyRewards;
use App\Filament\Traits\RequiresRole;
use App\Models\LoyaltyReward;
use App\Models\Product;
use App\Traits\HasPlanGating;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class LoyaltyRewardResource extends Resource
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): string
    {
        return 'manager';
    }

    protected static string $requiredPlan = 'pro';

    protected static ?string $model = LoyaltyReward::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-gift';

    protected static string|\UnitEnum|null $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 14;

    protected static ?string $navigationLabel = 'Loyalty Rewards';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(255),
            Textarea::make('description')
                ->rows(3),
            TextInput::make('points_required')
                ->required()
                ->numeric()
                ->minValue(1),
            Select::make('reward_type')
                ->required()
                ->options([
                    'percentage_discount' => 'Percentage Discount',
                    'fixed_discount' => 'Fixed Discount ($)',
                    'free_product' => 'Free Product',
                ])
                ->live(),
            TextInput::make('reward_value')
                ->required()
                ->numeric()
                ->minValue(0)
                ->label(fn (Get $get) => match ($get('reward_type')) {
                    'percentage_discount' => 'Discount Percentage (%)',
                    'fixed_discount' => 'Discount Amount ($)',
                    default => 'Reward Value',
                }),
            Select::make('product_id')
                ->label('Product')
                ->options(Product::where('is_active', true)->pluck('name', 'id'))
                ->searchable()
                ->visible(fn (Get $get) => $get('reward_type') === 'free_product'),
            Toggle::make('is_active')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('points_required')
                    ->sortable()
                    ->label('Points Required'),
                TextColumn::make('reward_type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'percentage_discount' => 'Percentage Off',
                        'fixed_discount' => 'Fixed Discount',
                        'free_product' => 'Free Product',
                        default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'percentage_discount' => 'info',
                        'fixed_discount' => 'success',
                        'free_product' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('reward_value')
                    ->formatStateUsing(function (mixed $state, LoyaltyReward $record) {
                        return match ($record->reward_type) {
                            'percentage_discount' => $state.'%',
                            'fixed_discount' => '$'.number_format((float) $state, 2),
                            'free_product' => $record->product?->name ?? '-',
                            default => $state,
                        };
                    })
                    ->label('Value'),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
            ])
            ->defaultSort('points_required');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Points' => $record->points_required,
            'Type' => $record->reward_type_label,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLoyaltyRewards::route('/'),
            'create' => CreateLoyaltyReward::route('/create'),
            'edit' => EditLoyaltyReward::route('/{record}/edit'),
        ];
    }
}
