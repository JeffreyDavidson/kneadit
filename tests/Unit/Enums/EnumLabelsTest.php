<?php

use App\Enums\Financial\CouponType;
use App\Enums\Marketing\EmailCampaignStatus;
use App\Enums\Marketing\SocialPlatform;
use App\Enums\Marketing\SocialPostStatus;
use App\Enums\Orders\PaymentStatus;

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
    expect(App\Enums\Platform\PlatformSenderType::Admin->getLabel())->toBe('Admin');
});

test('LoyaltyPointType has a label method', function () {
    expect(App\Enums\Engagement\LoyaltyPointType::Earned->getLabel())->toBe('Earned');
});

test('SupportTicketStatus has a label method', function () {
    expect(App\Enums\Platform\SupportTicketStatus::Open->getLabel())->toBe('Open');
});

test('SupportTicketPriority has a label method', function () {
    expect(App\Enums\Platform\SupportTicketPriority::High->getLabel())->toBe('High');
});

test('AnnouncementType has a label method', function () {
    expect(App\Enums\Platform\AnnouncementType::Info->getLabel())->toBe('Info');
});

test('SupportReplyAuthorType has a label method', function () {
    expect(App\Enums\Platform\SupportReplyAuthorType::Admin->getLabel())->toBe('Admin');
});
