<?php

namespace App\Policies\Orders;

use App\Enums\Staff\UserRole;
use App\Policies\Platform\RolePolicy;

class OrderPolicy extends RolePolicy
{
    #[\Override]
    protected UserRole $minimumRole = UserRole::Staff;
}
