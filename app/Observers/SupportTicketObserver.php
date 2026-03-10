<?php

namespace App\Observers;

use App\Models\AdminAuditLog;
use App\Models\SupportTicket;

class SupportTicketObserver
{
    public function created(SupportTicket $ticket): void
    {
        AdminAuditLog::log(
            action: 'ticket_opened',
            description: 'Support ticket opened: ' . $ticket->subject,
            targetType: 'support_ticket',
            targetId: (string) $ticket->id,
            metadata: [
                'tenant_id' => $ticket->tenant_id,
                'priority' => $ticket->priority,
            ],
        );
    }
}
