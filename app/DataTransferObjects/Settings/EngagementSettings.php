<?php

namespace App\DataTransferObjects\Settings;

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
        );
    }
}
