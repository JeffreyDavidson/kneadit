<?php

namespace App\Enums;

enum CateringEventType: string
{
    case Wedding = 'wedding';
    case Corporate = 'corporate';
    case Birthday = 'birthday';
    case Holiday = 'holiday';
    case Other = 'other';
}
