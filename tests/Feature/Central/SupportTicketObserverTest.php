<?php

use App\Models\AdminAuditLog;
use App\Models\SupportTicket;

beforeEach(function () {
    setUpCentralTest();
});

test('creating ticket creates audit log with ticket opened action', function () {
    $ticket = SupportTicket::query()->create([
        'tenant_id' => 'test-tenant',
        'subject' => 'Need help with orders',
        'body' => 'My orders are not showing up.',
        'status' => 'open',
        'priority' => 'normal',
    ]);

    $log = AdminAuditLog::query()->where('action', 'ticket_opened')->first();

    expect($log)->not->toBeNull();
    expect($log->target_type)->toBe('support_ticket');
    expect($log->target_id)->toBe((string) $ticket->id);
});

test('audit log description contains ticket subject', function () {
    SupportTicket::query()->create([
        'tenant_id' => 'test-tenant',
        'subject' => 'Billing issue with subscription',
        'body' => 'Please help.',
        'status' => 'open',
        'priority' => 'high',
    ]);

    $log = AdminAuditLog::query()->where('action', 'ticket_opened')->first();

    expect($log)->not->toBeNull();
    expect($log->description)->toContain('Billing issue with subscription');
});
