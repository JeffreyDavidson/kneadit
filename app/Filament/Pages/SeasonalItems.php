<?php

namespace App\Filament\Pages;

use App\Enums\UserRole;
use App\Filament\Traits\RequiresRole;
use App\Models\Product;
use App\Models\SeasonalItem;
use App\Traits\HasPlanGating;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Collection;

class SeasonalItems extends Page
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): UserRole
    {
        return UserRole::Manager;
    }

    protected static string $requiredPlan = 'pro';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static string|\UnitEnum|null $navigationGroup = 'Shop';

    protected static ?string $navigationLabel = 'Seasonal Items';

    protected static ?int $navigationSort = 12;

    protected string $view = 'filament.pages.seasonal-items';

    protected static ?string $title = 'Seasonal Items';

    public ?int $product_id = null;

    public ?string $available_from = null;

    public ?string $available_until = null;

    public ?string $notes = null;

    public function content(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Add Seasonal Item')
                ->schema([
                    Grid::make(4)->schema([
                        Select::make('product_id')
                            ->label('Product')
                            ->options(Product::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                        TextInput::make('available_from')
                            ->label('Available From')
                            ->type('date')
                            ->required(),
                        TextInput::make('available_until')
                            ->label('Available Until')
                            ->type('date')
                            ->required(),
                        TextInput::make('notes')
                            ->label('Notes')
                            ->placeholder('e.g. Summer special'),
                    ]),
                    Actions::make([
                        Action::make('addSeasonal')
                            ->label('Add Seasonal Item')
                            ->color('primary')
                            ->action('addSeasonalItem'),
                    ])->alignEnd(),
                ]),
        ]);
    }

    public function addSeasonalItem(): void
    {
        $this->validate([
            'product_id' => 'required|exists:products,id',
            'available_from' => 'required|date',
            'available_until' => 'required|date|after:available_from',
        ]);

        SeasonalItem::create([
            'product_id' => $this->product_id,
            'available_from' => $this->available_from,
            'available_until' => $this->available_until,
            'notes' => $this->notes,
        ]);

        $this->reset(['product_id', 'available_from', 'available_until', 'notes']);

        Notification::make()
            ->title('Seasonal item added!')
            ->success()
            ->send();
    }

    public function deleteSeasonalItem(int $id): void
    {
        SeasonalItem::findOrFail($id)->delete();

        Notification::make()
            ->title('Seasonal item removed.')
            ->success()
            ->send();
    }

    public function getCurrentItemsProperty(): Collection
    {
        return SeasonalItem::with('product')->current()->get();
    }

    public function getUpcomingItemsProperty(): Collection
    {
        return SeasonalItem::with('product')->upcoming()->orderBy('available_from')->get();
    }

    public function getExpiredItemsProperty(): Collection
    {
        return SeasonalItem::with('product')->expired()->orderByDesc('available_until')->get();
    }
}
