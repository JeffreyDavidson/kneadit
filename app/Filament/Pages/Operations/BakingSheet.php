<?php

namespace App\Filament\Pages\Operations;

use App\Enums\Staff\UserRole;
use App\Queries\Orders\BakingSheetQuery;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class BakingSheet extends Page
{
    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->hasMinRole(UserRole::Manager)) {
            return false;
        }

        return true;
    }

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedPrinter;

    protected static ?string $navigationLabel = 'Baking Sheet';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.operations.baking-sheet';

    public string $selectedDate = '';

    /** @var Collection<int, mixed> */
    public Collection $bakingItems;

    public function mount(): void
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->loadBakingSheet();
    }

    public function loadBakingSheet(): void
    {
        $this->bakingItems = BakingSheetQuery::forDate($this->selectedDate);
    }

    public function updatedSelectedDate(): void
    {
        $this->loadBakingSheet();
    }

    protected function getActions(): array
    {
        return [
            Action::make('print')
                ->label('Print')
                ->icon(Heroicon::OutlinedPrinter)
                ->action(fn () => $this->dispatch('print-page')),
        ];
    }
}
