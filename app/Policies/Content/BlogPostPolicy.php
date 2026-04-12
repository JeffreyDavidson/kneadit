<?php

namespace App\Policies\Content;

use App\Enums\Staff\UserRole;
use App\Policies\Platform\RolePolicy;

class BlogPostPolicy extends RolePolicy
{
    protected UserRole $minimumRole = UserRole::Staff;
}
