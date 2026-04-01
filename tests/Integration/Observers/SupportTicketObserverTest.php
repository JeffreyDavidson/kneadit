<?php

use App\Models\Platform\AdminAuditLog;
use App\Models\Platform\SupportTicket;

beforeEach(function () {
    setUpCentralTest();
});

test('creating ticket creates audit log with ticket opened action', function () {
    $ticket = SupportTicket::factory()->open()->create([
        'tenant_id' => 'test-tenant',
        'subject' => 'Need help with orders',
        'body' => 'My orders are not showing up.',
    ]);

    $log = AdminAuditLog::query()->where('action', 'ticket_opened')->first();

    expect($log)->not->toBeNull()->and($log->target_type)->toBe('support_ticket')->and($log->target_id)->toBe((string) $ticket->id);
});

test('audit log description contains ticket subject', function () {
    SupportTicket::factory()->open()->highPriority()->create([
        'tenant_id' => 'test-tenant',
        'subject' => 'Billing issue with subscription',
    ]);

    $log = AdminAuditLog::query()->where('action', 'ticket_opened')->first();

    expect($log)->not->toBeNull()->and($log->description)->toContain('Billing issue with subscription');
});
