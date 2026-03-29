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
