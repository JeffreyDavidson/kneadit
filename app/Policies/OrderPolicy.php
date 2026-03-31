<?php

namespace App\Policies;

use App\Enums\UserRole;

class OrderPolicy extends RolePolicy
{
    protected UserRole $minimumRole = UserRole::Staff;
}
