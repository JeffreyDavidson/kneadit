<?php

namespace App\Enums;

enum CateringInquiryStatus: string
{
    case Inquiry = 'inquiry';
    case Quoted = 'quoted';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
