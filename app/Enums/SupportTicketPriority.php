<?php

namespace App\Enums;

enum SupportTicketPriority: string
{
    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';
}
