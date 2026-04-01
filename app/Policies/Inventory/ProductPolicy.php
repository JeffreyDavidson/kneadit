<?php

namespace App\Policies\Inventory;

use App\Policies\Platform\RolePolicy;

use App\Enums\Staff\UserRole;

class ProductPolicy extends RolePolicy
{
    protected UserRole $minimumRole = UserRole::Staff;
}
