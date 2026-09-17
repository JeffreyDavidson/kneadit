<?php

declare(strict_types=1);

namespace App\Policies\Inventory;

use App\Enums\Staff\UserRole;
use App\Policies\Platform\RolePolicy;

class ProductPolicy extends RolePolicy
{
    #[\Override]
    protected UserRole $minimumRole = UserRole::Staff;
}
