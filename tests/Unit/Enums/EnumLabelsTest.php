<?php

use App\Enums\CouponType;
use App\Enums\EmailCampaignStatus;
use App\Enums\PaymentStatus;
use App\Enums\SocialPlatform;
use App\Enums\SocialPostStatus;

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
    expect(App\Enums\PlatformSenderType::Admin->getLabel())->toBe('Admin');
});

test('LoyaltyPointType has a label method', function () {
    expect(App\Enums\LoyaltyPointType::Earned->getLabel())->toBe('Earned');
});

test('SupportTicketStatus has a label method', function () {
    expect(App\Enums\SupportTicketStatus::Open->getLabel())->toBe('Open');
});

test('SupportTicketPriority has a label method', function () {
    expect(App\Enums\SupportTicketPriority::High->getLabel())->toBe('High');
});

test('AnnouncementType has a label method', function () {
    expect(App\Enums\AnnouncementType::Info->getLabel())->toBe('Info');
});

test('SupportReplyAuthorType has a label method', function () {
    expect(App\Enums\SupportReplyAuthorType::Admin->getLabel())->toBe('Admin');
});
