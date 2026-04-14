<?php

namespace App\Filament\Pages\Operations;

use App\Filament\Concerns\RequiresManagerRole;
use App\Queries\Orders\BakingSheetQuery;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class BakingSheet extends Page
{
    use RequiresManagerRole;

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
