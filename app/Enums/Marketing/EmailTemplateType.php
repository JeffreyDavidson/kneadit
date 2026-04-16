<?php

namespace App\Enums\Marketing;

use Filament\Support\Contracts\HasLabel;

enum EmailTemplateType: string implements HasLabel
{
    case OrderPlaced = 'order_placed';
    case OrderConfirmed = 'order_confirmed';
    case OrderBaking = 'order_baking';
    case OrderReady = 'order_ready';
    case OrderDelivered = 'order_delivered';
    case OrderCancelled = 'order_cancelled';
    case ReviewRequest = 'review_request';
    case HappyBirthday = 'happy_birthday';
    case RepeatOrderReminder = 'repeat_order_reminder';
    case ProductAvailable = 'product_available';

    public function getLabel(): string
    {
        return match ($this) {
            self::OrderPlaced => 'Order Placed',
            self::OrderConfirmed => 'Order Confirmed',
            self::OrderBaking => 'Order Baking',
            self::OrderReady => 'Order Ready',
            self::OrderDelivered => 'Order Delivered',
            self::OrderCancelled => 'Order Cancelled',
            self::ReviewRequest => 'Review Request',
            self::HappyBirthday => 'Happy Birthday',
            self::RepeatOrderReminder => 'Repeat Order Reminder',
            self::ProductAvailable => 'Product Available',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::OrderPlaced => 'Sent when a customer submits a new order.',
            self::OrderConfirmed => 'Sent when you confirm a customer\'s order.',
            self::OrderBaking => 'Sent when an order moves to the baking stage.',
            self::OrderReady => 'Sent when an order is ready for pickup or delivery.',
            self::OrderDelivered => 'Sent when an order has been delivered.',
            self::OrderCancelled => 'Sent when an order is cancelled.',
            self::ReviewRequest => 'Sent after delivery to ask the customer for a review.',
            self::HappyBirthday => 'Sent on the customer\'s birthday with an optional coupon.',
            self::RepeatOrderReminder => 'Sent to re-engage customers who haven\'t ordered recently.',
            self::ProductAvailable => 'Sent when a waitlisted product is back in stock.',
        };
    }

    /**
     * @return array<int, string>
     */
    public function availablePlaceholders(): array
    {
        $common = ['{customer_name}', '{store_name}'];

        return match ($this) {
            self::OrderPlaced => [...$common, '{order_number}', '{order_total}'],
            self::OrderConfirmed => [...$common, '{order_number}', '{order_total}', '{delivery_date}'],
            self::OrderBaking => [...$common, '{order_number}'],
            self::OrderReady => [...$common, '{order_number}'],
            self::OrderDelivered => [...$common, '{order_number}'],
            self::OrderCancelled => [...$common, '{order_number}', '{order_total}'],
            self::ReviewRequest => [...$common, '{order_number}', '{review_url}'],
            self::HappyBirthday => [...$common, '{coupon_code}', '{coupon_amount}'],
            self::RepeatOrderReminder => [...$common, '{days_since_last_order}'],
            self::ProductAvailable => [...$common, '{product_name}'],
        };
    }

    public function defaultSubject(): string
    {
        return match ($this) {
            self::OrderPlaced => 'Order #{order_number} Received — {store_name}',
            self::OrderConfirmed => 'Order #{order_number} Confirmed — {store_name}',
            self::OrderBaking => 'Your Order #{order_number} is Being Prepared — {store_name}',
            self::OrderReady => 'Order #{order_number} is Ready! — {store_name}',
            self::OrderDelivered => 'Order #{order_number} Delivered — {store_name}',
            self::OrderCancelled => 'Order #{order_number} Cancelled — {store_name}',
            self::ReviewRequest => 'How was your order from {store_name}? ⭐',
            self::HappyBirthday => '🎂 Happy Birthday, {customer_name}! A Sweet Gift From {store_name}',
            self::RepeatOrderReminder => 'We Miss You! 🥖 Your Favorite Treats Are Waiting',
            self::ProductAvailable => '{product_name} is back at {store_name}!',
        };
    }
}
