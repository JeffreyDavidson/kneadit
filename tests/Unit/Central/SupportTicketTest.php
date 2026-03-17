<?php

namespace Tests\Unit\Central;

use App\Models\SupportReply;
use App\Models\SupportTicket;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\CentralTestCase;

class SupportTicketTest extends CentralTestCase
{
    public function test_can_create_ticket(): void
    {
        $ticket = SupportTicket::create([
            'subject' => 'Help needed',
            'body' => 'Something is broken',
            'status' => 'open',
            'priority' => 'high',
            'tenant_id' => 'tenant-1',
        ]);

        $found = SupportTicket::where('subject', 'Help needed')->first();
        $this->assertNotNull($found);
        $this->assertEquals('high', $found->priority);
    }

    public function test_status_defaults_to_open(): void
    {
        $ticket = SupportTicket::create([
            'subject' => 'Test',
            'body' => 'Body',
            'tenant_id' => 't1',
        ]);

        $this->assertEquals('open', $ticket->fresh()->status);
    }

    public function test_priority_defaults_to_normal(): void
    {
        $ticket = SupportTicket::create([
            'subject' => 'Test',
            'body' => 'Body',
            'tenant_id' => 't1',
        ]);

        $this->assertEquals('normal', $ticket->fresh()->priority);
    }

    public function test_replies_relationship(): void
    {
        $ticket = SupportTicket::create([
            'subject' => 'Test',
            'body' => 'Body',
            'tenant_id' => 't1',
        ]);

        SupportReply::create(['ticket_id' => $ticket->id, 'body' => 'Reply 1']);
        SupportReply::create(['ticket_id' => $ticket->id, 'body' => 'Reply 2']);

        $this->assertCount(2, $ticket->replies);
    }

    public function test_tenant_relationship_exists(): void
    {
        $ticket = SupportTicket::create(['subject' => 'T', 'body' => 'B', 'tenant_id' => 't1']);

        $this->assertInstanceOf(BelongsTo::class, $ticket->tenant());
    }
}
