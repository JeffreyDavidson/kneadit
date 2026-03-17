<?php

use App\Models\AdminAuditLog;
use Tests\CentralTestCase;

uses(CentralTestCase::class);

test('log creates record', function () {
    $log = AdminAuditLog::log('tenant.suspend', 'Suspended tenant', 'tenant', 'tenant-1', ['reason' => 'abuse']);

    $found = AdminAuditLog::where('action', 'tenant.suspend')->first();
    expect($found)->not->toBeNull();
    expect($found->description)->toBe('Suspended tenant');
    expect($found->target_type)->toBe('tenant');
    expect($found->target_id)->toBe('tenant-1');
    expect($found->metadata)->toBe(['reason' => 'abuse']);
});

test('log works with minimal params', function () {
    $log = AdminAuditLog::log('login', 'Admin logged in');

    expect(AdminAuditLog::where('action', 'login')->first())->not->toBeNull();
    expect($log->target_type)->toBeNull();
});

test('scope for action', function () {
    AdminAuditLog::log('login', 'Logged in');
    AdminAuditLog::log('logout', 'Logged out');

    expect(AdminAuditLog::forAction('login')->get())->toHaveCount(1);
});

test('scope for target', function () {
    AdminAuditLog::log('update', 'Updated', 'tenant', 't1');
    AdminAuditLog::log('update', 'Updated', 'user', 'u1');

    expect(AdminAuditLog::forTarget('tenant', 't1')->get())->toHaveCount(1);
    expect(AdminAuditLog::forTarget('user')->get())->toHaveCount(1);
});

test('scope recent', function () {
    $log = AdminAuditLog::log('test', 'Recent');

    $results = AdminAuditLog::recent()->get();
    expect($results->contains($log))->toBeTrue();
});
