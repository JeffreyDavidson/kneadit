<?php

namespace App\Enums;

enum PlatformSenderType: string
{
    case Admin = 'admin';
    case Tenant = 'tenant';
}
