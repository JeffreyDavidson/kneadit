<?php

use App\Actions\Platform\LogAuditEntry;
use App\Models\AdminAuditLog;

beforeEach(fn () => setUpCentralTest());

test('log creates record', function () {
    $log = resolve(LogAuditEntry::class)('tenant.suspend', 'Suspended tenant', 'tenant', 'tenant-1', ['reason' => 'abuse']);

    $found = AdminAuditLog::query()->where('action', 'tenant.suspend')->first();
    expect($found)->not->toBeNull();
    expect($found->description)->toBe('Suspended tenant');
    expect($found->target_type)->toBe('tenant');
    expect($found->target_id)->toBe('tenant-1');
    expect($found->metadata)->toBe(['reason' => 'abuse']);
});

test('log works with minimal params', function () {
    $log = resolve(LogAuditEntry::class)('login', 'Admin logged in');

    expect(AdminAuditLog::query()->where('action', 'login')->first())->not->toBeNull();
    expect($log->target_type)->toBeNull();
});

test('scope for action', function () {
    resolve(LogAuditEntry::class)('login', 'Logged in');
    resolve(LogAuditEntry::class)('logout', 'Logged out');

    expect(AdminAuditLog::forAction('login')->get())->toHaveCount(1);
});

test('scope for target', function () {
    resolve(LogAuditEntry::class)('update', 'Updated', 'tenant', 't1');
    resolve(LogAuditEntry::class)('update', 'Updated', 'user', 'u1');

    expect(AdminAuditLog::forTarget('tenant', 't1')->get())->toHaveCount(1);
    expect(AdminAuditLog::forTarget('user')->get())->toHaveCount(1);
});

test('scope recent', function () {
    $log = resolve(LogAuditEntry::class)('test', 'Recent');

    $results = AdminAuditLog::recent()->get();
    expect($results->contains($log))->toBeTrue();
});
