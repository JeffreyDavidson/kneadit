<?php

use App\Models\Operations\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('activity log created_at defaults to now when not set', function () {
    $log = ActivityLog::factory()->create([
        'user_name' => 'Test User',
        'action' => 'created',
        'description' => 'Test description',
    ]);

    expect($log->created_at)->not->toBeNull();
});
