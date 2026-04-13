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
}
