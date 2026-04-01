<?php

namespace App\Filament\Resources\Reviews\Tables;

use App\Actions\Content\ApproveReview;
use App\Actions\Content\FeatureReview;
use App\Models\Engagement\Review;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['product']))
            ->columns([
                TextColumn::make('customer_name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('customer_email')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('product.name')
                    ->label('Product')
                    ->sortable()
                    ->searchable()
                    ->placeholder('General review'),

                BadgeColumn::make('rating')
                    ->colors([
                        'danger' => fn (int $state) => $state <= 2,
                        'warning' => 3,
                        'success' => fn (int $state) => $state >= 4,
                    ])
                    ->formatStateUsing(fn (int $state) => $state . ' ★'),

                TextColumn::make('comment')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (Str::length($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        return $state;
                    }),

                ToggleColumn::make('is_approved')
                    ->label('Approved'),

                ToggleColumn::make('is_featured')
                    ->label('Featured'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('rating')
                    ->options([
                        1 => '1 Star',
                        2 => '2 Stars',
                        3 => '3 Stars',
                        4 => '4 Stars',
                        5 => '5 Stars',
                    ]),

                SelectFilter::make('is_approved')
                    ->options([
                        1 => 'Approved',
                        0 => 'Pending',
                    ]),

                SelectFilter::make('is_featured')
                    ->options([
                        1 => 'Featured',
                        0 => 'Not Featured',
                    ]),

                SelectFilter::make('product_id')
                    ->relationship('product', 'name')
                    ->label('Product'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->icon(Heroicon::OutlinedCheck)
                    ->color('success')
                    ->action(fn (Review $record) => resolve(ApproveReview::class)($record))
                    ->visible(fn (Review $record) => ! $record->is_approved),

                Action::make('feature')
                    ->icon(Heroicon::OutlinedStar)
                    ->color('warning')
                    ->action(fn (Review $record) => resolve(FeatureReview::class)($record))
                    ->visible(fn (Review $record) => ! $record->is_featured && $record->is_approved),

                EditAction::make()
                    ->slideOver()
                    ->modalWidth('md'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No reviews yet')
            ->emptyStateDescription('Customer reviews will appear here.');
    }
}
