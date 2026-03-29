<?php

namespace App\Filament\Pages;

use App\Enums\SubscriptionTier;
use App\Enums\UserRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Product;
use App\Models\SeasonalItem;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Pennant\Feature;

class SeasonalItems extends Page
{
    use ShowsUpgradeBadge;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->hasMinRole(UserRole::Manager)) {
            return false;
        }

        return Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

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
                            ->options(Product::query()->where('is_active', true)->pluck('name', 'id'))
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
            'product_id' => ['required', 'exists:products,id'],
            'available_from' => ['required', 'date'],
            'available_until' => ['required', 'date', 'after:available_from'],
        ]);

        SeasonalItem::query()->create([
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
        SeasonalItem::query()->findOrFail($id)->delete();

        Notification::make()
            ->title('Seasonal item removed.')
            ->success()
            ->send();
    }

    /** @return Collection<int, SeasonalItem> */
    public function getCurrentItemsProperty(): Collection
    {
        return SeasonalItem::with('product')->current()->get();
    }

    /** @return Collection<int, SeasonalItem> */
    public function getUpcomingItemsProperty(): Collection
    {
        return SeasonalItem::with('product')->upcoming()->orderBy('available_from')->get();
    }

    /** @return Collection<int, SeasonalItem> */
    public function getExpiredItemsProperty(): Collection
    {
        return SeasonalItem::with('product')->expired()->orderByDesc('available_until')->get();
    }
}
