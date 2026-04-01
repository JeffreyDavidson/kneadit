<?php

namespace App\Policies\Content;

use App\Policies\Platform\RolePolicy;

use App\Enums\Staff\UserRole;

class BlogPostPolicy extends RolePolicy
{
    protected UserRole $minimumRole = UserRole::Staff;
}
