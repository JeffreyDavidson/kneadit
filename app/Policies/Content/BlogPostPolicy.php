<?php

declare(strict_types=1);

namespace App\Policies\Content;

use App\Enums\Staff\UserRole;
use App\Policies\Platform\RolePolicy;

class BlogPostPolicy extends RolePolicy
{
    #[\Override]
    protected UserRole $minimumRole = UserRole::Staff;
}
