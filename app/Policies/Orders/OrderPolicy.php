<?php

namespace App\Policies\Orders;

use App\Policies\Platform\RolePolicy;

use App\Enums\Staff\UserRole;

class OrderPolicy extends RolePolicy
{
    protected UserRole $minimumRole = UserRole::Staff;
}
