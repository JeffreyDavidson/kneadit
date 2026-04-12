<?php

namespace App\Policies\Inventory;

use App\Enums\Staff\UserRole;
use App\Policies\Platform\RolePolicy;

class ProductPolicy extends RolePolicy
{
    protected UserRole $minimumRole = UserRole::Staff;
}
