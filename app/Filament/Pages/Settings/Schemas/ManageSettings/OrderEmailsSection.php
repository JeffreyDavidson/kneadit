<?php

namespace App\Filament\Pages\Settings\Schemas\ManageSettings;

use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class OrderEmailsSection
{
    public static function make(): Section
    {
        // Tenants can disable individual transactional emails (e.g., skip "Baking"
        // status emails but keep "Ready").
        return Section::make('Order Emails')
            ->description('Choose which order emails are sent to your customers. Disabling an email stops sending it entirely; customers will still see order status changes on the tracking page.')
            ->schema([
                Grid::make(2)
                    ->schema([
                        Toggle::make('email_order_placed_enabled')
                            ->label('Order Placed')
                            ->helperText('Sent when a customer completes checkout.'),

                        Toggle::make('email_order_confirmed_enabled')
                            ->label('Order Confirmed')
                            ->helperText('Sent when you confirm the order.'),

                        Toggle::make('email_order_baking_enabled')
                            ->label('Baking')
                            ->helperText('Sent when you start baking the order.'),

                        Toggle::make('email_order_ready_enabled')
                            ->label('Ready')
                            ->helperText('Sent when the order is ready for pickup or delivery.'),

                        Toggle::make('email_order_delivered_enabled')
                            ->label('Delivered')
                            ->helperText('Sent when the order is delivered or picked up.'),

                        Toggle::make('email_order_cancelled_enabled')
                            ->label('Cancelled')
                            ->helperText('Sent when an order is cancelled.'),

                        Toggle::make('email_order_message_enabled')
                            ->label('Order Message Replies')
                            ->helperText('Sent to the customer when you reply to their order message. Does not affect the notification you receive when a customer messages you.'),

                        Toggle::make('email_product_available_enabled')
                            ->label('Back-in-Stock')
                            ->helperText('Sent to waitlisted customers when you use "Notify Waitlist" on a product.'),
                    ]),
            ]);
    }
}
