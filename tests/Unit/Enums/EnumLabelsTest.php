<?php

use App\Enums\Engagement\LoyaltyPointType;
use App\Enums\Financial\CouponType;
use App\Enums\Marketing\EmailCampaignStatus;
use App\Enums\Marketing\SocialPlatform;
use App\Enums\Marketing\SocialPostStatus;
use App\Enums\Orders\PaymentStatus;
use App\Enums\Platform\AnnouncementType;
use App\Enums\Platform\PlatformSenderType;
use App\Enums\Platform\SupportReplyAuthorType;
use App\Enums\Platform\SupportTicketPriority;
use App\Enums\Platform\SupportTicketStatus;

test('PaymentStatus has a label method', function () {
    expect(PaymentStatus::Paid->getLabel())->toBe('Paid');
});

test('CouponType has a label method', function () {
    expect(CouponType::Percentage->getLabel())->toBe('Percentage');
});

test('EmailCampaignStatus has a label method', function () {
    expect(EmailCampaignStatus::Draft->getLabel())->toBe('Draft');
});

test('SocialPostStatus has a label method', function () {
    expect(SocialPostStatus::Draft->getLabel())->toBe('Draft');
});

test('SocialPlatform has a label method', function () {
    expect(SocialPlatform::Instagram->getLabel())->toBe('Instagram');
});

test('PlatformSenderType has a label method', function () {
    expect(PlatformSenderType::Admin->getLabel())->toBe('Admin');
});

test('LoyaltyPointType has a label method', function () {
    expect(LoyaltyPointType::Earned->getLabel())->toBe('Earned');
});

test('SupportTicketStatus has a label method', function () {
    expect(SupportTicketStatus::Open->getLabel())->toBe('Open');
});

test('SupportTicketPriority has a label method', function () {
    expect(SupportTicketPriority::High->getLabel())->toBe('High');
});

test('AnnouncementType has a label method', function () {
    expect(AnnouncementType::Info->getLabel())->toBe('Info (Gold)');
});

test('SupportReplyAuthorType has a label method', function () {
    expect(SupportReplyAuthorType::Admin->getLabel())->toBe('Admin');
});
