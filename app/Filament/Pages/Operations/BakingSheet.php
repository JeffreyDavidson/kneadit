<?php

namespace App\Filament\Pages\Operations;

use App\Filament\Concerns\RequiresManagerRole;
use App\Models\Orders\OrderItem;
use App\Queries\Orders\BakingSheetQuery;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;

class BakingSheet extends Page
{
    use RequiresManagerRole;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedPrinter;

    protected static ?string $navigationLabel = 'Baking Sheet';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.operations.baking-sheet';

    public string $selectedDate = '';

    public function mount(): void
    {
        $this->selectedDate = now()->format('Y-m-d');
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Baking Sheet')
                ->schema([
                    DatePicker::make('selectedDate')
                        ->label('Date')
                        ->required()
                        ->live(),
                ]),
        ]);
    }

    /**
     * Recompute on every render rather than storing as Livewire state.
     * The query result should not become Livewire state because state
     * serialization can mangle the aggregate attributes on each OrderItem — the
     * Print action's dispatch caused the re-rendered HTML to show the
     * empty state because the Collection deserialised broken.
     *
     * @return Collection<int, OrderItem>
     */
    #[Computed]
    public function bakingItems(): Collection
    {
        return BakingSheetQuery::forDate($this->selectedDate);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label('Print')
                ->icon(Heroicon::OutlinedPrinter)
                ->action(fn () => $this->dispatch('print-page')),
        ];
    }
}
