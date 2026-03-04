<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use App\Services\PayPalService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('customer.name')
                    ->sortable()
                    ->searchable(),

                BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'pending',
                        'warning' => 'confirmed',
                        'info' => 'baking',
                        'success' => 'ready',
                        'primary' => 'delivered',
                        'danger' => 'cancelled',
                    ]),

                BadgeColumn::make('payment_status')
                    ->label('Payment')
                    ->colors([
                        'danger' => 'unpaid',
                        'success' => 'paid',
                        'gray' => 'cancelled',
                        'warning' => 'refunded',
                    ]),

                TextColumn::make('total')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('requested_date')
                    ->date()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Baker')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'baking' => 'Baking',
                        'ready' => 'Ready',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ]),

                SelectFilter::make('payment_status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'paid' => 'Paid',
                        'cancelled' => 'Cancelled',
                        'refunded' => 'Refunded',
                    ]),

                Filter::make('requested_date')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['from'],
                                fn ($query, $date) => $query->whereDate('requested_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn ($query, $date) => $query->whereDate('requested_date', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                Action::make('confirm')
                    ->icon('heroicon-o-check-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Confirm Order')
                    ->modalDescription('Are you sure you want to confirm this order?')
                    ->action(function (Order $record) {
                        $record->update(['status' => 'confirmed']);
                        Notification::make()
                            ->title('Order confirmed')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Order $record) => $record->status === 'pending'),

                Action::make('start_baking')
                    ->label('Start Baking')
                    ->icon('heroicon-o-fire')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Start Baking')
                    ->modalDescription('Mark this order as currently being baked?')
                    ->action(function (Order $record) {
                        $record->update(['status' => 'baking']);
                        Notification::make()
                            ->title('Order marked as baking')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Order $record) => $record->status === 'confirmed'),

                Action::make('mark_ready')
                    ->label('Mark Ready')
                    ->icon('heroicon-o-clock')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Mark Ready')
                    ->modalDescription('Mark this order as ready for pickup/delivery?')
                    ->action(function (Order $record) {
                        $record->update(['status' => 'ready']);
                        Notification::make()
                            ->title('Order marked as ready')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Order $record) => $record->status === 'baking'),

                Action::make('mark_delivered')
                    ->label('Mark Delivered')
                    ->icon('heroicon-o-truck')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Mark Delivered')
                    ->modalDescription('Mark this order as delivered/completed?')
                    ->action(function (Order $record) {
                        $record->update(['status' => 'delivered']);
                        Notification::make()
                            ->title('Order marked as delivered')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Order $record) => $record->status === 'ready'),

                Action::make('cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cancel Order')
                    ->modalDescription('Are you sure you want to cancel this order? This action cannot be undone.')
                    ->action(function (Order $record) {
                        $record->update(['status' => 'cancelled']);
                        Notification::make()
                            ->title('Order cancelled')
                            ->warning()
                            ->send();
                    })
                    ->visible(fn (Order $record) => in_array($record->status, ['pending', 'confirmed', 'baking'])),

                Action::make('send_paypal_invoice')
                    ->label('Send PayPal Invoice')
                    ->icon('heroicon-o-credit-card')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Send PayPal Invoice')
                    ->modalDescription('This will create and send a PayPal invoice to the customer for payment.')
                    ->action(function (Order $record) {
                        $paypalService = app(PayPalService::class);
                        $invoiceId = $paypalService->createAndSendInvoice($record);
                        
                        if ($invoiceId) {
                            Notification::make()
                                ->title('PayPal invoice sent successfully')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Failed to send PayPal invoice')
                                ->body('Please check the logs for more details.')
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (Order $record) => 
                        $record->payment_status === 'unpaid' && 
                        !$record->paypal_invoice_id &&
                        in_array($record->status, ['confirmed', 'baking', 'ready'])
                    ),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
