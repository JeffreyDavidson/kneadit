<?php

namespace App\Filament\Resources\Products\Tables;

use App\Actions\Products\NotifyProductWaitlist;
use App\Filament\Actions\AuthorizedDeleteBulkAction;
use App\Filament\Actions\SlideOverEditAction;
use App\Filament\Tables\Columns\MoneyColumn;
use App\Models\Inventory\Product;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with('category')
                ->withCount([
                    'waitlistEntries' => fn (Builder $q) => $q->whereNull('notified_at'),
                ]))
            ->columns([
                ImageColumn::make('image')
                    ->circular()
                    ->defaultImageUrl(asset('images/product-placeholder.svg')),

                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('category.name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                MoneyColumn::make('price')
                    ->sortable()
                    ->toggleable(),

                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->toggleable(),

                ToggleColumn::make('is_featured')
                    ->label('Featured')
                    ->toggleable(),

                TextColumn::make('waitlist_count')
                    ->label('Waitlist')
                    ->getStateUsing(fn (Product $record) => $record->waitlist_entries_count)
                    ->badge()
                    ->color(fn (int $state) => $state > 0 ? 'warning' : 'gray')
                    ->formatStateUsing(fn (int $state) => $state > 0 ? "{$state} waiting" : '—')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category'),

                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),

                SelectFilter::make('is_featured')
                    ->label('Featured')
                    ->options([
                        1 => 'Yes',
                        0 => 'No',
                    ]),
            ])
            ->recordActions([
                Action::make('printLabel')
                    ->label('Print label')
                    ->icon(Heroicon::OutlinedPrinter)
                    ->color('gray')
                    ->url(fn (Product $record) => route('admin.products.label', $record))
                    ->openUrlInNewTab(),
                Action::make('notifyWaitlist')
                    ->label(fn (Product $record): string => 'Notify Waitlist (' . ($record->waitlist_entries_count ?? 0) . ')')
                    ->icon(Heroicon::OutlinedBellAlert)
                    ->color('warning')
                    ->visible(fn (Product $record): bool => ($record->waitlist_entries_count ?? 0) > 0)
                    ->requiresConfirmation()
                    ->modalHeading(fn (Product $record) => "Notify {$record->waitlist_entries_count} customer(s) that {$record->name} is back?")
                    ->modalDescription('Each customer will be emailed and marked as notified so they won\'t be re-emailed on the next run.')
                    ->action(function (Product $record): void {
                        $count = resolve(NotifyProductWaitlist::class)($record);

                        Notification::make()
                            ->title($count > 0 ? "Queued {$count} back-in-stock email(s)" : 'Nothing sent — product_available emails are disabled in Manage Settings')
                            ->{$count > 0 ? 'success' : 'warning'}()
                            ->send();
                    }),
                SlideOverEditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    AuthorizedDeleteBulkAction::make(),
                ]),
            ])
            ->deferColumnManager(false)
            ->defaultSort('name')
            ->emptyStateHeading('No products yet')
            ->emptyStateDescription('Add your first product to start building your catalog.');
    }
}
