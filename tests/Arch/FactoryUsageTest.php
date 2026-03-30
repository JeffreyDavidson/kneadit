<?php

declare(strict_types=1);

/**
 * Ensure test files use factories instead of Model::query()->create() for models that have factories.
 */
test('test files should not use query()->create() for models with factories', function () {
    $violations = [];
    $testsDir = dirname(__DIR__);

    $modelPatterns = [
        'User::query()->create(',
        'Customer::query()->create(',
        'Order::query()->create(',
        'Product::query()->create(',
        'Category::query()->create(',
        'Ingredient::query()->create(',
        'BlogPost::query()->create(',
        'SocialPost::query()->create(',
        'EmailCampaign::query()->create(',
        'GiftCard::query()->create(',
        'Coupon::query()->create(',
        'Review::query()->create(',
        'Supplier::query()->create(',
        'Survey::query()->create(',
        'SurveyResponse::query()->create(',
        'LoyaltyReward::query()->create(',
        'LoyaltyPoint::query()->create(',
        'Expense::query()->create(',
        'Income::query()->create(',
        'Holiday::query()->create(',
        'BlockedDate::query()->create(',
        'StaffInvitation::query()->create(',
        'PlatformAnnouncement::query()->create(',
        'PlatformMessage::query()->create(',
        'SupportTicket::query()->create(',
        'Tenant::query()->create(',
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
