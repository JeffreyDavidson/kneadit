<?php

namespace App\Policies;

use App\Enums\UserRole;

class ProductPolicy extends RolePolicy
{
    protected UserRole $minimumRole = UserRole::Staff;
}
