<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\View\TablesIconAlias;

/**
 * The funnel button above a table. Filament sets its badge to the active-filter
 * count on every render, and the count 0 comes through as the text "0", so a
 * table with no filters set still shows a "0" badge. This leaves the badge off
 * until a filter is actually set.
 *
 * Mirrors `Table::getFiltersTriggerAction()`; keep it in step with that method.
 */
class FiltersTriggerAction extends Action
{
    public static function forTable(Table $table): static
    {
        return static::make('openFilters')
            ->label(__('filament-tables::table.actions.filter.label'))
            ->iconButton()
            ->icon(FilamentIcon::resolve(TablesIconAlias::ACTIONS_FILTER) ?? Heroicon::Funnel)
            ->color('gray')
            ->livewireClickHandlerEnabled(false)
            ->modalSubmitAction(false)
            ->extraModalFooterActions([
                $table->getFiltersApplyAction()
                    ->close(),
                Action::make('resetFilters')
                    ->label(__('filament-tables::table.filters.actions.reset.label'))
                    ->color('danger')
                    ->action('resetTableFiltersForm')
                    ->button(),
            ])
            ->modalCancelActionLabel(__('filament::components/modal.actions.close.label'))
            ->table($table)
            ->authorize(true);
    }

    #[\Override]
    public function getBadge(): ?string
    {
        $badge = parent::getBadge();

        return $badge === '0' ? null : $badge;
    }
}
