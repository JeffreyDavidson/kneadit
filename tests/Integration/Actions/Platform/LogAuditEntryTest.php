<?php

use App\Actions\Platform\LogAuditEntry;
use App\Models\Platform\AdminAuditLog;
use App\Models\Staff\User;

use function Pest\Laravel\actingAs;

beforeEach(fn () => setUpCentralTest());

test('it creates an audit log entry', function () {
    $user = User::factory()->create();
    actingAs($user);

    $entry = resolve(LogAuditEntry::class)(
        action: 'tenant.created',
        description: 'Created a new tenant',
        targetType: 'tenant',
        targetId: 'test-bakery',
        metadata: ['plan' => 'starter'],
    );

    expect($entry)
        ->toBeInstanceOf(AdminAuditLog::class)
        ->action->toBe('tenant.created')
        ->description->toBe('Created a new tenant')
        ->target_type->toBe('tenant')
        ->target_id->toBe('test-bakery')
        ->admin_id->toBe($user->id);

    expect($entry->metadata)->toBe(['plan' => 'starter']);
});

test('it creates an entry without optional fields', function () {
    $user = User::factory()->create();
    actingAs($user);

    $entry = resolve(LogAuditEntry::class)(
        action: 'login',
        description: 'Admin logged in',
    );

    expect($entry->target_type)->toBeNull()
        ->and($entry->target_id)->toBeNull()
        ->and($entry->metadata)->toBeNull();
});
