<?php

namespace App\DataTransferObjects\Settings;

use App\Enums\Orders\OrderStatus;

final readonly class EngagementSettings
{
    public function __construct(
        public bool $birthdayProgramEnabled,
        public bool $birthdayCouponEnabled,
        public int $birthdayDiscountPercentage,
        public int $birthdayCouponValidDays,
        public bool $reviewRequestsEnabled,
        public int $reviewRequestDelayHours,
        public bool $repeatRemindersEnabled,
        public int $repeatReminderDays,
        public bool $announcementEnabled,
        public string $announcementText,
        public string $announcementType,
        public bool $emailOrderPlacedEnabled,
        public bool $emailOrderConfirmedEnabled,
        public bool $emailOrderBakingEnabled,
        public bool $emailOrderReadyEnabled,
        public bool $emailOrderDeliveredEnabled,
        public bool $emailOrderCancelledEnabled,
        public bool $emailOrderMessageEnabled,
        public bool $emailProductAvailableEnabled,
    ) {}

    public static function resolve(): self
    {
        return new self(
            birthdayProgramEnabled: settings('birthday_program_enabled', '0') === '1',
            birthdayCouponEnabled: settings('birthday_coupon_enabled', '1') === '1',
            birthdayDiscountPercentage: (int) settings('birthday_discount_percentage', '15'),
            birthdayCouponValidDays: (int) settings('birthday_coupon_valid_days', '7'),
            reviewRequestsEnabled: settings('review_requests_enabled', '0') === '1',
            reviewRequestDelayHours: (int) settings('review_request_delay_hours', '24'),
            repeatRemindersEnabled: settings('repeat_reminders_enabled', '0') === '1',
            repeatReminderDays: (int) settings('repeat_reminder_days', '30'),
            announcementEnabled: settings('announcement_enabled', '0') === '1',
            announcementText: (string) settings('announcement_text', ''),
            announcementType: (string) settings('announcement_type', 'info'),
            // Per-email toggles default true (backwards compat for existing tenants).
            emailOrderPlacedEnabled: settings('email_order_placed_enabled', '1') === '1',
            emailOrderConfirmedEnabled: settings('email_order_confirmed_enabled', '1') === '1',
            emailOrderBakingEnabled: settings('email_order_baking_enabled', '1') === '1',
            emailOrderReadyEnabled: settings('email_order_ready_enabled', '1') === '1',
            emailOrderDeliveredEnabled: settings('email_order_delivered_enabled', '1') === '1',
            emailOrderCancelledEnabled: settings('email_order_cancelled_enabled', '1') === '1',
            emailOrderMessageEnabled: settings('email_order_message_enabled', '1') === '1',
            emailProductAvailableEnabled: settings('email_product_available_enabled', '1') === '1',
        );
    }

    /**
     * Map an OrderStatus to the toggle that gates its email.
     */
    public function isOrderStatusEmailEnabled(OrderStatus $status): bool
    {
        return match ($status) {
            OrderStatus::Confirmed => $this->emailOrderConfirmedEnabled,
            OrderStatus::Baking => $this->emailOrderBakingEnabled,
            OrderStatus::Ready => $this->emailOrderReadyEnabled,
            OrderStatus::Delivered => $this->emailOrderDeliveredEnabled,
            OrderStatus::Cancelled => $this->emailOrderCancelledEnabled,
            default => true,
        };
    }
}
