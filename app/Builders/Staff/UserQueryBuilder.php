<?php

namespace App\Builders\Staff;

use App\Enums\Staff\UserRole;
use App\Models\Staff\User;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<User> */
class UserQueryBuilder extends Builder
{
    public function withRole(UserRole $role): static
    {
        $this->where('role', $role);

        return $this;
    }

    public function owners(): static
    {
        return $this->withRole(UserRole::Owner);
    }
}
