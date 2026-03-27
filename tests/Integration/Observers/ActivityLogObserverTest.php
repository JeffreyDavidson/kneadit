<?php

use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('activity log created_at defaults to now when not set', function () {
    $log = ActivityLog::query()->create([
        'user_name' => 'Test User',
        'action' => 'test',
        'description' => 'Test description',
    ]);

    expect($log->created_at)->not->toBeNull();
});
