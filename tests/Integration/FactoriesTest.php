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
            'App\\Models\\BlogPost',
            'App\\Models\\Tenant',
            'App\\Models\\EmailCampaign',
            'App\\Models\\PlatformActivity',
            'App\\Models\\PlatformAnnouncement',
            'App\\Models\\PlatformMessage',
            'App\\Models\\PlatformSetting',
            'App\\Models\\AdminAuditLog',
            'App\\Models\\SupportTicket',
            'App\\Models\\ScheduledCheckin',
            'App\\Models\\FeatureUsageLog',
            'App\\Models\\TenantNote',
            'App\\Models\\SupportReply',
            'App\\Models\\CheckinLog',
            'App\\Models\\EmailCampaignLog',
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
