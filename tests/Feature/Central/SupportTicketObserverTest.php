<?php

namespace Tests\Feature\Central;

use App\Models\AdminAuditLog;
use App\Models\SupportTicket;
use Tests\CentralTestCase;

class SupportTicketObserverTest extends CentralTestCase
{
    public function test_creating_ticket_creates_audit_log_with_ticket_opened_action(): void
    {
        $ticket = SupportTicket::create([
            'tenant_id' => 'test-tenant',
            'subject' => 'Need help with orders',
            'body' => 'My orders are not showing up.',
            'status' => 'open',
            'priority' => 'normal',
        ]);

        $log = AdminAuditLog::where('action', 'ticket_opened')->first();
        $this->assertNotNull($log);
        $this->assertEquals('support_ticket', $log->target_type);
        $this->assertEquals((string) $ticket->id, $log->target_id);
    }

    public function test_audit_log_description_contains_ticket_subject(): void
    {
        SupportTicket::create([
            'tenant_id' => 'test-tenant',
            'subject' => 'Billing issue with subscription',
            'body' => 'Please help.',
            'status' => 'open',
            'priority' => 'high',
        ]);

        $log = AdminAuditLog::where('action', 'ticket_opened')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('Billing issue with subscription', $log->description);
    }
}
