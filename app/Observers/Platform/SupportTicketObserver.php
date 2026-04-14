<?php

namespace App\Observers\Platform;

use App\Actions\Platform\LogAuditEntry;
use App\Models\Platform\SupportTicket;

class SupportTicketObserver
{
    public function __construct(
        private LogAuditEntry $logAuditEntry,
    ) {}

    public function created(SupportTicket $ticket): void
    {
        ($this->logAuditEntry)(
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
