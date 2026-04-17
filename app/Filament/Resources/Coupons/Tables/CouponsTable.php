<?php

namespace App\Filament\Resources\Coupons\Tables;

use App\Enums\Financial\CouponType;
use App\Filament\Actions\SlideOverEditAction;
use App\Models\Financial\Coupon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->badge(),

                TextColumn::make('value')
                    ->money('USD')
                    ->formatStateUsing(function (mixed $state, Coupon $record) {
                        if ($record->type === CouponType::Percentage) {
                            return $state . '%';
                        }

                        return Number::currency($state);
                    }),

                TextColumn::make('min_order_amount')
                    ->money('USD')
                    ->placeholder('No minimum'),

                TextColumn::make('usage')
                    ->formatStateUsing(function (mixed $state, Coupon $record) {
                        if ($record->max_uses) {
                            return "{$record->used_count} / {$record->max_uses}";
                        }

                        return "{$record->used_count} (unlimited)";
                    }),

                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never'),

                IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(CouponType::class),

                TernaryFilter::make('is_active'),

                SelectFilter::make('status')
                    ->options([
                        'valid' => 'Valid',
                        'expired' => 'Expired',
                        'maxed_out' => 'Max Uses Reached',
                    ])
                    ->query(function (Builder $query, array $state) {
                        /** @var Builder<Coupon> $query */
                        return match ($state['value'] ?? null) {
                            'valid' => $query->valid(),
                            'expired' => $query->where('expires_at', '<', now()),
                            'maxed_out' => $query->whereNotNull('max_uses')->whereColumn('used_count', '>=', 'max_uses'),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                SlideOverEditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No coupons yet')
            ->emptyStateDescription('Create a coupon to offer discounts to your customers.');
    }
}
