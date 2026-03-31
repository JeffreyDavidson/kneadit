<?php

namespace App\Policies;

use App\Enums\UserRole;

class BlogPostPolicy extends RolePolicy
{
    protected UserRole $minimumRole = UserRole::Staff;
}
