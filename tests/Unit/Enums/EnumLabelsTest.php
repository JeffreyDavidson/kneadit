<?php

use App\Enums\CouponType;
use App\Enums\EmailCampaignStatus;
use App\Enums\PaymentStatus;
use App\Enums\SocialPlatform;
use App\Enums\SocialPostStatus;

test('PaymentStatus has a label method', function () {
    expect(PaymentStatus::Paid->label())->toBe('Paid');
});

test('CouponType has a label method', function () {
    expect(CouponType::Percentage->label())->toBe('Percentage');
});

test('EmailCampaignStatus has a label method', function () {
    expect(EmailCampaignStatus::Draft->label())->toBe('Draft');
});

test('SocialPostStatus has a label method', function () {
    expect(SocialPostStatus::Draft->label())->toBe('Draft');
});

test('SocialPlatform has a label method', function () {
    expect(SocialPlatform::Instagram->label())->toBe('Instagram');
});
