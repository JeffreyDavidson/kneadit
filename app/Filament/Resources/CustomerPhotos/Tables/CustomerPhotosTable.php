<?php

namespace App\Filament\Resources\CustomerPhotos\Tables;

use App\Actions\Content\ApproveCustomerPhoto;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class CustomerPhotosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['product']))
            ->columns([
                ImageColumn::make('photo_path')
                    ->label('Photo')
                    ->disk('public')
                    ->width(80)
                    ->height(60),

                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('caption')
                    ->limit(40)
                    ->toggleable(),

                TextColumn::make('product.name')
                    ->label('Product')
                    ->placeholder('—')
                    ->toggleable(),

                ToggleColumn::make('is_approved')
                    ->label('Approved'),

                ToggleColumn::make('is_featured')
                    ->label('Featured'),

                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_approved')
                    ->label('Approval')
                    ->boolean()
                    ->trueLabel('Approved')
                    ->falseLabel('Pending')
                    ->native(false),

                TernaryFilter::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->trueLabel('Featured')
                    ->falseLabel('Not featured')
                    ->native(false),
            ])
            ->recordActions([
                EditAction::make()->slideOver()->modalWidth('md'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approve')
                        ->label('Approve')
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->action(fn (Collection $records) => $records->each(fn ($record) => resolve(ApproveCustomerPhoto::class)($record)))
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No customer photos yet')
            ->emptyStateDescription('Photos submitted by customers will appear here for approval.');
    }
}
