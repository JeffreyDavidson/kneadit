<?php

declare(strict_types=1);

/**
 * Ensure test files use factories instead of Model::query()->create() for models that have factories.
 */
test('test files should not use query()->create() for models with factories', function () {
    $violations = [];
    $testsDir = dirname(__DIR__);

    $modelPatterns = [
        'ActivityLog::query()->create(',
        'BlockedDate::query()->create(',
        'BlogPost::query()->create(',
        'BusinessSchedule::query()->create(',
        'Category::query()->create(',
        'Coupon::query()->create(',
        'Customer::query()->create(',
        'CustomerFavorite::query()->create(',
        'CustomerPhoto::query()->create(',
        'EmailCampaign::query()->create(',
        'Expense::query()->create(',
        'GiftCard::query()->create(',
        'Holiday::query()->create(',
        'Income::query()->create(',
        'Ingredient::query()->create(',
        'LoyaltyPoint::query()->create(',
        'LoyaltyReward::query()->create(',
        'OrderItem::query()->create(',
        'OrderMessage::query()->create(',
        'PlatformActivity::query()->create(',
        'PlatformAnnouncement::query()->create(',
        'PlatformMessage::query()->create(',
        'Product::query()->create(',
        'ProductImage::query()->create(',
        'Referral::query()->create(',
        'Review::query()->create(',
        'Setting::query()->create(',
        'SocialPost::query()->create(',
        'StaffInvitation::query()->create(',
        'Supplier::query()->create(',
        'SupportReply::query()->create(',
        'SupportTicket::query()->create(',
        'Survey::query()->create(',
        'SurveyResponse::query()->create(',
        'Tenant::query()->create(',
        'TenantNote::query()->create(',
        'User::query()->create(',
        'Order::query()->create(',
    ];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($testsDir, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        if (str_contains($file->getPathname(), DIRECTORY_SEPARATOR . 'Arch' . DIRECTORY_SEPARATOR)) {
            continue;
        }

        $content = file_get_contents($file->getPathname());
        $relative = str_replace(realpath($testsDir) . DIRECTORY_SEPARATOR, '', $file->getPathname());

        foreach ($modelPatterns as $pattern) {
            if (str_contains($content, $pattern)) {
                $violations[] = "{$relative}: use factory instead of {$pattern})";
            }
        }
    }

    expect($violations)->toBeEmpty(
        "Use Model::factory() instead of Model::query()->create():\n" . implode("\n", $violations),
    );
});
