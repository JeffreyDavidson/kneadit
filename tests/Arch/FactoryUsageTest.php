<?php

declare(strict_types=1);

/**
 * Ensure test files use factories instead of direct Model::create() or
 * Model::query()->create() for models that have factories.
 */
test('test files should not use direct Model::create() for models with factories', function () {
    $models = [
        'ActivityLog',
        'BlockedDate',
        'BlogPost',
        'BusinessSchedule',
        'Category',
        'Coupon',
        'Customer',
        'CustomerFavorite',
        'CustomerNote',
        'CustomerPhoto',
        'EmailCampaign',
        'EmailTemplate',
        'Expense',
        'GiftCard',
        'Holiday',
        'ImpersonationToken',
        'Income',
        'Ingredient',
        'LoyaltyPoint',
        'LoyaltyReward',
        'Order',
        'OrderItem',
        'OrderMessage',
        'PlatformActivity',
        'PlatformAnnouncement',
        'PlatformMessage',
        'PlatformSetting',
        'Product',
        'ProductImage',
        'Referral',
        'Review',
        'Setting',
        'SocialPost',
        'StaffInvitation',
        'Supplier',
        'SupportReply',
        'SupportTicket',
        'Survey',
        'SurveyResponse',
        'Tenant',
        'TenantNote',
        'User',
    ];

    $patterns = [];
    foreach ($models as $model) {
        // Direct static call (e.g. User::create([...]))
        $patterns[] = "/(?<![A-Za-z0-9_\\\\]){$model}::create\(/";
        // query() chain (e.g. User::query()->create([...]))
        $patterns[] = "/(?<![A-Za-z0-9_\\\\]){$model}::query\(\)->create\(/";
    }

    $testsDir = dirname(__DIR__);
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($testsDir, FilesystemIterator::SKIP_DOTS),
    );

    $violations = [];

    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        if (str_contains($file->getPathname(), DIRECTORY_SEPARATOR . 'Arch' . DIRECTORY_SEPARATOR)) {
            continue;
        }

        $content = file_get_contents($file->getPathname());
        $relative = str_replace(realpath($testsDir) . DIRECTORY_SEPARATOR, '', $file->getPathname());

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $violations[] = "{$relative}: use factory instead of {$matches[0]})";
            }
        }
    }

    expect($violations)->toBeEmpty(
        "Use Model::factory() instead of Model::create() / Model::query()->create():\n" . implode("\n", $violations),
    );
});
