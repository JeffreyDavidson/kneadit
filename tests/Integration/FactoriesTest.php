<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('every model with a factory can be created', function () {
    fake()->unique(true); // Reset unique counters
    $factoryFiles = glob(database_path('factories/*.php'));
    $failures = [];

    foreach ($factoryFiles as $file) {
        $factoryClass = 'Database\\Factories\\' . basename($file, '.php');

        if (! class_exists($factoryClass)) {
            continue;
        }

        $modelClass = (new $factoryClass)->modelName();

        if (! class_exists($modelClass)) {
            continue;
        }

        // Skip models that need central connection or special setup
        $skipModels = [
            'App\\Models\\Content\\BlogPost',
            'App\\Models\\Platform\\Tenant',
            'App\\Models\\Engagement\\EmailCampaign',
            'App\\Models\\Platform\\PlatformActivity',
            'App\\Models\\Platform\\PlatformAnnouncement',
            'App\\Models\\Platform\\PlatformMessage',
            'App\\Models\\Platform\\PlatformSetting',
            'App\\Models\\Platform\\AdminAuditLog',
            'App\\Models\\Platform\\SupportTicket',
            'App\\Models\\Operations\\ScheduledCheckin',
            'App\\Models\\Platform\\FeatureUsageLog',
            'App\\Models\\Platform\\TenantNote',
            'App\\Models\\Platform\\SupportReply',
            'App\\Models\\Operations\\CheckinLog',
            'App\\Models\\Engagement\\EmailCampaignLog',
        ];

        if (in_array($modelClass, $skipModels)) {
            continue;
        }

        try {
            $model = $modelClass::factory()->make();
            expect($model)->toBeInstanceOf($modelClass);
        } catch (Throwable $e) {
            $failures[] = basename($file, '.php') . ': ' . $e->getMessage();
        }
    }

    expect($failures)->toBeEmpty(
        "Factory creation failures:\n" . implode("\n", $failures),
    );
});
